<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
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

        $subsplashMediaItems = $subsplash->subsplashFetch($relatedMediaItemsUrl);

        $subsplashMediaItemMap = [];
        $responseData['mediaItems'] = [];
        foreach (data_get($subsplashMediaItems, '_embedded.media-items', []) as $mediaItem) {

            $subsplashMediaItemMap[$mediaItem['id']] = $mediaItem;

            $mediaItemEntry = Entry::query()
                ->find($mediaItem['id']);



            // If the associated media item isn't there, create it.
            if ($mediaItemEntry == null) {

                $entry = Entry::make()
                    ->published(true)
                    ->blueprint('subsplash_media_item')
                    ->collection('media_item')
                    ->id($mediaItem['id'])
                    ->slug($mediaItem['slug']);

                $date = Carbon::now();

                $entry->date($date);
                $data = [
                    'title' => $mediaItem['title'],
                    'subsplash_media_item_id' => $mediaItem['id'],
                ];

                if (isset($mediaItem['_embedded']) && isset($mediaItem['_embedded']['media-series'])) {
                    $seriesId = $mediaItem['_embedded']['media-series']['id'];
                    $data['series'] = "entry::$seriesId";
                }

                $entry->data($data);

                $entry->save();

                $mediaItemEntry = $entry;
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
