<?php

namespace App\Builders;

use Statamic\Facades\Entry;
use Statamic\Contracts\Entries\QueryBuilder;

class EntryQueryBuilder
{
    private QueryBuilder $query;

    private function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    public static function query(): self
    {
        return new self(Entry::query());
    }

    // Collection scopes

    public static function mediaItems(): self
    {
        return self::query()->inCollection('media_item');
    }

    public static function mediaSeries(): self
    {
        return self::query()->inCollection('media_series');
    }

    public static function prayers(): self
    {
        return self::query()->inCollection('prayers');
    }

    public static function blogs(): self
    {
        return self::query()->inCollection('blogs');
    }

    public static function authors(): self
    {
        return self::query()->inCollection('authors');
    }

    public static function events(): self
    {
        return self::query()->inCollection('events');
    }

    public static function pages(): self
    {
        return self::query()->inCollection('pages');
    }

    public static function experiences(): self
    {
        return self::query()->inCollection('experiences');
    }

    public static function alerts(): self
    {
        return self::query()->inCollection('alerts');
    }

    public static function podcasts(): self
    {
        return self::query()->inCollection('podcasts');
    }

    // Common filters

    public function inCollection(string $collection): self
    {
        $this->query->where('collection', $collection);

        return $this;
    }

    public function published(): self
    {
        $this->query->where('published', true);

        return $this;
    }

    public function bySlug(string $slug): self
    {
        $this->query->where('slug', $slug);

        return $this;
    }

    public function byBlueprint(string $blueprint): self
    {
        $this->query->where('blueprint', $blueprint);

        return $this;
    }

    /**
     * Filter to entries that are either past their publish date or marked as publicly visible.
     * Handles the common pattern: date <= now() OR make_scheduled_page_public = true
     */
    public function visibleOrScheduled(): self
    {
        $this->query->where(function ($query) {
            $query->where('date', '<=', now())
                ->orWhere('data->make_scheduled_page_public', true);
        });

        return $this;
    }

    public function inSeries(string $seriesId): self
    {
        $this->query->where('data->series', 'like', "%{$seriesId}");

        return $this;
    }

    public function recentFirst(): self
    {
        $this->query->orderBy('date', 'desc');

        return $this;
    }

    public function oldestFirst(): self
    {
        $this->query->orderBy('date', 'asc');

        return $this;
    }

    public function pastPublishDate(): self
    {
        $this->query->where('date', '<=', now());

        return $this;
    }

    // Terminal methods that delegate to the underlying query builder

    public function get()
    {
        return $this->query->get();
    }

    public function first()
    {
        return $this->query->first();
    }

    public function firstOrFail()
    {
        return $this->query->firstOrFail();
    }

    public function find(string $id)
    {
        return $this->query->find($id);
    }

    public function paginate(int $perPage = 15)
    {
        return $this->query->paginate($perPage);
    }

    public function count(): int
    {
        return $this->query->count();
    }

    public function limit(int $limit): self
    {
        $this->query->limit($limit);

        return $this;
    }

    /**
     * Proxy any other method calls to the underlying Statamic query builder.
     */
    public function __call(string $method, array $parameters): mixed
    {
        $result = $this->query->{$method}(...$parameters);

        if ($result instanceof QueryBuilder) {
            return $this;
        }

        return $result;
    }
}
