<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SitemapPagesQuery
{
    /**
     * Reads the stored uri column instead of hydrating Statamic entries,
     * because rebuilding each uri runs the computed route fields and
     * their per entry queries.
     *
     * @return array<int, array{
     *     loc: string,
     *     lastmod: string,
     *     priority: float
     * }>
     */
    public function get(): array
    {
        return $this->entries()
            ->where('collection', '!=', 'alerts')
            ->whereNotNull('uri')
            ->where(function (Builder $query): void {
                $query->where('data->hide_from_search_engines', false)
                    ->orWhereNull('data->hide_from_search_engines');
            })
            ->get(['uri', 'updated_at', 'created_at', 'data->sitemap_priority as sitemap_priority'])
            ->map(fn (Model $entry): array => [
                'loc' => $entry->uri,
                'lastmod' => ($entry->updated_at ?? $entry->created_at)->format('Y-m-d'),
                'priority' => (float) ($entry->sitemap_priority ?? 0.5),
            ])
            ->all();
    }

    private function entries(): Builder
    {
        $model = config('statamic.eloquent-driver.entries.model');

        return $model::query();
    }
}
