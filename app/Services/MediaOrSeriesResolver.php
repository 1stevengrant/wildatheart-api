<?php

namespace App\Services;

use Statamic\Entries\Entry;
use App\Builders\EntryQueryBuilder;
use Statamic\Eloquent\Entries\UuidEntryModel;

class MediaOrSeriesResolver
{
    /**
     * Resolve a single-segment slug or two-segment URI fragment to a media
     * item or media series entry.
     *
     * When the input is a single segment that resolves to a published
     * `subsplash_media_series` containing exactly one published, non-private
     * media item, the resolver substitutes that item entry so callers
     * receive the item payload instead of the series payload. Pass
     * `$swapSingleItemSeries = false` to opt out of that substitution.
     */
    public function resolve(string $slugOrUri, bool $swapSingleItemSeries = true): Entry|UuidEntryModel|null
    {
        $slug = $slugOrUri;
        $uri = str_contains($slugOrUri, '/') ? '/media/' . $slugOrUri : $slugOrUri;

        $entry = EntryQueryBuilder::query()
            ->where(function ($query) {
                $query->where('collection', 'media_item')
                    ->orWhere('collection', 'media_series');
            })
            ->where(function ($query) use ($slug, $uri) {
                $query->where('slug', $slug)
                    ->orWhere('uri', $uri);
            })
            ->published()
            ->visibleOrScheduled()
            ->first();

        if (! $entry) {
            return null;
        }

        if (
            $swapSingleItemSeries
            && ! str_contains($slugOrUri, '/')
            && $entry->blueprint->handle === 'subsplash_media_series'
        ) {
            if ($soleItem = $this->soleVisibleItem($entry)) {
                return $soleItem;
            }
        }

        return $entry;
    }

    /**
     * Return the only published, non-private media item in the given series,
     * or null when the series has zero or 2+ qualifying items.
     */
    private function soleVisibleItem(Entry|UuidEntryModel $series): Entry|UuidEntryModel|null
    {
        $items = EntryQueryBuilder::mediaItems()
            ->inSeries($series->id)
            ->published()
            ->visibleOrScheduled()
            ->limit(2)
            ->get();

        return $items->count() === 1 ? $items->first() : null;
    }
}
