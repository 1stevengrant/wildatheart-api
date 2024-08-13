<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\SubsplashService;
use Carbon\Carbon;
use Statamic\Entries\Entry;

class MediaItemDataController extends Controller
{
    public function show($slug)
    {

        $responseData = [];

        $mediaItem = Entry::query()
            ->where('collection', 'media_item')
            ->where('slug', $slug)
            ->firstOrFail();

        $responseData['id'] = $mediaItem->id;
        $responseData['slug'] = $mediaItem->slug;
        $responseData['uri'] = $mediaItem->uri;

        $subsplash = new SubsplashService;

        $subsplashMediaItem = $subsplash->subsplashFetch("https://core.subsplash.com/media/v1/media-items/{$mediaItem->id}");

        if (isset($subsplashSeries['errors']) && $subsplashMediaItem['errors'][0]['code'] === 'resource_not_found') {
            return response()->json($mediaItem);
        }

        $subsplashSeries = $subsplashMediaItem['_embedded']['media-series'];

        $seriesId = $subsplashSeries['id'];

        $responseData['title'] = $subsplashMediaItem['title'];

        // Handle scenario where Subsplash API isn't returning subtitle
        if (isset($subsplashMediaItem['subtitle'])) {
            $responseData['subtitle'] = $subsplashMediaItem['subtitle'];
        }

        $responseData['summary'] = $subsplashMediaItem['summary'];
        $responseData['summary_text'] = $subsplashMediaItem['summary_text'];
        $responseData['short_code'] = $subsplashMediaItem['short_code'];
        $responseData['series'] = $subsplashSeries;
        $seriesEntry = Entry::query()
            ->where('id', $seriesId)
            ->first();

        if (! $seriesEntry) {
            $subsplashSeries = $subsplash->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$seriesId}");
            $entry = Entry::make()
                ->published(true)
                ->blueprint('subsplash_media_series')
                ->collection('media_series')
                ->id($subsplashSeries['id'])
                ->slug($subsplashSeries['title']);
            $date = Carbon::now();

            $entry->date($date);

            $entry->data([
                'title' => $subsplashSeries['title'],
                'subsplash_media_series_id' => $subsplashSeries['id'],
            ]);

            $entry->save();
            $seriesEntry = $entry;
        }

        $responseData['series']['uri'] = $seriesEntry->uri;
        $relatedMediaItemsUrl = $subsplashSeries['_links']['media-items']['href'];
        $responseData['_embedded'] = $subsplashMediaItem['_embedded'];

        $subsplashMediaItems = $subsplash->subsplashFetch($relatedMediaItemsUrl);

        $subsplashMediaItemMap = [];

        foreach ($subsplashMediaItems['_embedded']['media-items'] as $mediaItem) {
            $subsplashMediaItemMap[$mediaItem['id']] = $mediaItem;

            $mediaItemEntry = Entry::query()
                ->find($mediaItem['id']);

            if ($mediaItemEntry == null) {

                $mediaItemEntry = Entry::make()
                    ->published(true)
                    ->blueprint('subsplash_media_item')
                    ->collection('media_item')
                    ->id($mediaItem['id'])
                    ->slug($mediaItem['slug']);

                $date = Carbon::now();

                $mediaItemEntry->date($date);
                $data = [
                    'title' => $mediaItem['title'],
                    'subsplash_media_item_id' => $mediaItem['id'],
                ];

                if (isset($mediaItem['_embedded']) && isset($mediaItem['_embedded']['media-series'])) {
                    $seriesId = $mediaItem['_embedded']['media-series']['id'];
                    $data['series'] = "entry::$seriesId";
                }

                $mediaItemEntry->data($data);

                $mediaItemEntry->save();
            }
            if (! isset($mediaItemEntry['title']) || ! isset($mediaItemEntry['subsplash_media_item_id']) || ! isset($mediaItemEntry['series'])) {
                $data = [
                    'title' => $mediaItem['title'],
                    'subsplash_media_item_id' => $mediaItem['id'],
                    'series' => "entry::$seriesId",
                ];
                $mediaItemEntry->data($data);
                $mediaItemEntry->save();
            }
        }

        $mediaItemEntries = Entry::query()
            ->where('collection', 'media_item')
            ->where('data->series', 'like', "%{$seriesId}")
            ->get();

        $responseData['mediaItems'] = [];

        foreach ($mediaItemEntries as $mediaItemEntry) {

            // Handle scenario where Subsplash API isn't returning accurately
            if (! isset($subsplashMediaItemMap[$mediaItemEntry->id])) {
                $mediaItem = $subsplash->subsplashFetch("https://core.subsplash.com/media/v1/media-items/{$mediaItemEntry->id}");
                $subsplashMediaItemMap[$mediaItemEntry->id] = $mediaItem;
            }

            $responseData['mediaItems'][] = [
                ...$subsplashMediaItemMap[$mediaItemEntry->id],
                'slug' => $mediaItemEntry->slug,
                'uri' => $mediaItemEntry->uri,
            ];
        }

        // Return the view with the merged data
        return response()->json($responseData);
    }
}
