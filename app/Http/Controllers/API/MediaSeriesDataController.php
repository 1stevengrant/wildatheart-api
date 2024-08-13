<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SubsplashService;
use Carbon\Carbon;
use Statamic\Entries\Entry;

class MediaSeriesDataController extends Controller
{
    public function show($slug)
    {
        $responseData = [];

        $series = Entry::query()
            ->where('collection', 'media_series')
            ->where('slug', $slug)
            ->firstOrFail();

        $responseData['id'] = $series->id;
        $responseData['title'] = $series->title;
        $responseData['slug'] = $series->slug;
        $responseData['uri'] = $series->uri;

        if ($series->blueprint === 'the_series') {

            return $series;
        }

        $subsplash = new SubsplashService;

        $subsplashSeries = $subsplash->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$series->subsplash_media_series_id}");
        $responseData['subtitle'] = $subsplashSeries['subtitle'];

        $series['subsplash_data'] = $subsplashSeries;

        $relatedMediaItemsUrl = $subsplashSeries['_links']['media-items']['href'];
        $responseData['_embedded'] = $subsplashSeries['_embedded'];

        $subsplashMediaItems = $subsplash->subsplashFetch($relatedMediaItemsUrl);

        $subsplashMediaItemMap = [];
        $responseData['mediaItems'] = [];
        foreach ($subsplashMediaItems['_embedded']['media-items'] as $mediaItem) {

            $subsplashMediaItemMap[$mediaItem['id']] = $mediaItem;

            $mediaItemEntry = Entry::query()
                ->find($mediaItem['id']);

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
            }

            $responseData['mediaItems'][] = [
                ...$mediaItem,
                'slug' => $mediaItemEntry->slug,
                'uri' => $mediaItemEntry->uri,
            ];
        }

        // Return the view with the merged data
        return response()->json($responseData);
    }
}
