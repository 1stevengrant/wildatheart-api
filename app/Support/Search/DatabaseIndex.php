<?php

namespace App\Support\Search;

use Illuminate\Support\Str;
use Statamic\Search\Documents;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Statamic\Search\Index as BaseIndex;

class DatabaseIndex extends BaseIndex
{
    public function search($query): DatabaseQuery
    {
        return (new DatabaseQuery($this))->query($query);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function lookup(?string $query): Collection
    {
        $terms = Str::of($query ?? '')->lower()->squish()->explode(' ')->filter();

        if ($terms->isEmpty()) {
            return collect();
        }

        return $this->documents()
            ->where(function (Builder $builder) use ($terms): void {
                foreach ($terms as $term) {
                    $builder->where('content', 'like', '%' . $this->escapeLike($term) . '%');
                }
            })
            ->get()
            ->map(function (object $document) use ($query): array {
                $fields = json_decode($document->fields, true);

                return [
                    ...$fields,
                    'reference' => $document->reference,
                    'search_score' => $this->score($fields, $query),
                ];
            })
            ->sortByDesc('search_score')
            ->values();
    }

    public function exists(): bool
    {
        return DB::table('search_indexes')->where('name', $this->name)->exists();
    }

    public function delete($document): void
    {
        $this->documents()->where('reference', $document->getSearchReference())->delete();
    }

    public function insertDocuments(Documents $documents): void
    {
        $now = now();

        $documents
            ->map(fn (array $fields, string $reference): array => [
                'index' => $this->name,
                'reference' => $reference,
                'fields' => json_encode($fields),
                'content' => $this->content($fields),
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->chunk(500)
            ->each(fn (Collection $rows) => DB::table('search_documents')->upsert(
                $rows->values()->all(),
                ['index', 'reference'],
                ['fields', 'content', 'updated_at'],
            ));
    }

    /**
     * Statamic only calls this at the start of a rebuild, so clearing the
     * documents is also the point where the index starts to exist.
     */
    protected function deleteIndex(): void
    {
        $this->documents()->delete();

        DB::table('search_indexes')->upsert(
            ['name' => $this->name, 'built_at' => now()],
            ['name'],
            ['built_at'],
        );
    }

    protected function documents(): Builder
    {
        return DB::table('search_documents')->where('index', $this->name);
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    protected function content(array $fields): string
    {
        return Str::lower(
            collect($fields)->flatten()->filter(fn (mixed $value): bool => is_scalar($value))->implode(' ')
        );
    }

    /**
     * Earlier fields in the index config weigh more, so title matches rank first.
     *
     * @param  array<string, mixed>  $fields
     */
    protected function score(array $fields, string $query): int
    {
        $query = Str::lower(mb_trim($query));
        $configuredFields = $this->config['fields'] ?? ['title'];

        return collect($configuredFields)
            ->values()
            ->sum(function (string $field, int $position) use ($fields, $query, $configuredFields): int {
                if (! Str::contains($this->content([$fields[$field] ?? null]), $query)) {
                    return 0;
                }

                return count($configuredFields) - $position;
            });
    }

    protected function escapeLike(string $term): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term);
    }
}
