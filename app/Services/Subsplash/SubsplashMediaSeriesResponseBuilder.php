<?php

namespace App\Services\Subsplash;

use Statamic\Entries\Entry;
use Illuminate\Http\JsonResponse;
use Statamic\Eloquent\Entries\UuidEntryModel;

class SubsplashMediaSeriesResponseBuilder
{
    public function __construct(private readonly SubsplashApiClient $apiClient) {}

    public function build(Entry|UuidEntryModel $series): array|JsonResponse
    {
        $responseData = [];

        $responseData['id'] = $series->id;
        $responseData['title'] = $series->title;
        $responseData['slug'] = $series->slug;
        $responseData['uri'] = $series->uri;
        $responseData['blueprint'] = $series->blueprint;

        $subsplashSeries = $this->apiClient->getMediaSeries($series->subsplash_media_series_id);

        if (isset($subsplashSeries['errors']) && $subsplashSeries['errors'][0]['code'] === 'resource_not_found') {
            return response()->json($series);
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

        $subsplashMediaItemMap = [];
        $responseData['mediaItems'] = [];
        foreach ($allItems as $mediaItem) {
            $subsplashMediaItemMap[$mediaItem['id']] = $mediaItem;

            $mediaItemEntry = Entry::query()
                ->where('id', $mediaItem['id'])
                ->orWhere('data->subsplash_media_item_id', $mediaItem['id'])
                ->first();

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
