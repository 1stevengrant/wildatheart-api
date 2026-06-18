<?php

namespace App\Services\Subsplash;

use Statamic\Entries\Entry;
use Statamic\Eloquent\Entries\UuidEntryModel;

class SubsplashMediaSeriesResponseBuilder
{
    public function __construct(private readonly SubsplashApiClient $apiClient) {}

    public function build(Entry|UuidEntryModel $series): array
    {
        $responseData = [];

        $responseData['id'] = $series->id;
        $responseData['title'] = $series->title;
        $responseData['slug'] = $series->slug;
        $responseData['uri'] = $series->uri;
        $responseData['blueprint'] = $series->blueprint;

        $subsplashSeries = $this->apiClient->getMediaSeries($series->subsplash_media_series_id);

        if (isset($subsplashSeries['errors']) && $subsplashSeries['errors'][0]['code'] === 'resource_not_found') {
            $responseData['subtitle'] = '';
            $responseData['subsplash_data'] = $subsplashSeries;
            $responseData['_embedded'] = '';
            $responseData['mediaItems'] = [];

            return $responseData;
        }

        $responseData['subtitle'] = data_get($subsplashSeries, 'subtitle', '');

        $responseData['subsplash_data'] = $subsplashSeries;

        $relatedMediaItemsUrl = data_get($subsplashSeries, '_links.media-items.href', '');
        $responseData['_embedded'] = data_get($subsplashSeries, '_embedded', '');

        $allItems = [];

        $nextUrl = $relatedMediaItemsUrl;
        while ($nextUrl) {
            $subsplashMediaItems = $this->apiClient->fetch($nextUrl . '&filter[status]=published');
            $allItems = array_merge($allItems, data_get($subsplashMediaItems, '_embedded.media-items', []));
            $nextUrl = data_get($subsplashMediaItems, '_links.next.href');
        }

        $itemIds = array_column($allItems, 'id');

        $matchingEntries = Entry::query()
            ->where(function ($query) use ($itemIds) {
                $query->whereIn('id', $itemIds)
                    ->orWhereIn('data->subsplash_media_item_id', $itemIds);
            })
            ->where('published', true)
            ->where(function ($query) {
                $query->where('date', '<=', now())
                    ->orWhere('data->make_scheduled_page_public', true);
            })
            ->get();

        $entriesByItemId = [];
        foreach ($matchingEntries as $entry) {
            $entriesByItemId[$entry->id()] = $entry;

            if ($subsplashMediaItemId = $entry->get('subsplash_media_item_id')) {
                $entriesByItemId[$subsplashMediaItemId] = $entry;
            }
        }

        $responseData['mediaItems'] = [];
        foreach ($allItems as $mediaItem) {
            $mediaItemEntry = $entriesByItemId[$mediaItem['id']] ?? null;

            if ($mediaItemEntry === null) {
                continue;
            }

            $responseData['mediaItems'][] = [
                ...$mediaItem,
                'slug' => $mediaItemEntry->slug,
                'uri' => $mediaItemEntry->uri,
            ];
        }

        return $responseData;
    }
}
