<?php

namespace App\Http\Controllers\API;

use Statamic\Entries\Entry;
use App\Services\SubsplashService;
use App\Http\Controllers\Controller;
use Statamic\Eloquent\Entries\UuidEntryModel;

class MediaSeriesDataController extends Controller
{
    public function show(string $slug)
    {

        $series = Entry::query()
            ->where('collection', 'media_series')
            ->where('slug', $slug)
            ->firstOrFail();

        if ($series->blueprint === 'custom_series') {
            return $series;
        }

        $responseData = $this->buildSubsplashMediaSeriesResponse($series);        // Return the view with the merged data

        // Return the view with the merged data
        return response()->json($responseData);
    }

    public function buildSubsplashMediaSeriesResponse(Entry|UuidEntryModel $series)
    {
        $responseData = [];

        $responseData['id'] = $series->id;
        $responseData['title'] = $series->title;
        $responseData['slug'] = $series->slug;
        $responseData['uri'] = $series->uri;
        $responseData['blueprint'] = $series->blueprint;

        $subsplash = new SubsplashService;

        $subsplashSeries = $subsplash->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$series->subsplash_media_series_id}");

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
            $subsplashMediaItems = $subsplash->subsplashFetch($nextUrl . "&filter[status]=published");
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

            // The media hasn't been imported yet. Skip it.
            if ($mediaItemEntry == null) {
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
