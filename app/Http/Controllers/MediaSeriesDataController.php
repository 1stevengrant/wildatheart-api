<?php

namespace App\Http\Controllers;

use Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Statamic\Entries\Entry;

class MediaSeriesDataController extends Controller
{
    public function getAccessToken()
    {
        $SUBSPLASH_CLIENT_ID = env('SUBSPLASH_CLIENT_ID');
        $SUBSPLASH_CLIENT_SECRET = env('SUBSPLASH_CLIENT_SECRET');
        $SUBSPLASH_SCOPE = env('SUBSPLASH_SCOPE');
        $SUBSPLASH_AUTHENTICATION_TOKEN_URL = env('SUBSPLASH_AUTHENTICATION_TOKEN_URL');

        $response = Http::asForm()->post('https://core.subsplash.com/tokens/v1/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $SUBSPLASH_CLIENT_ID,
            'client_secret' => $SUBSPLASH_CLIENT_SECRET,
            'scope' => "app:{$SUBSPLASH_SCOPE}",
        ]);

        if ($response->successful()) {
            $data = $response->json();

            return $data['access_token'];
        }

        throw new \Exception('Failed to fetch access token');
    }

    public function subsplashFetch($url)
    {
        $accessToken = $this->getAccessToken();
        $token = 'Bearer '.$accessToken;

        $response = Http::withHeaders([
            'Accept' => 'application/vnd.api+json',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Authorization' => $token,
            'Cache-Control' => 'no-cache',
        ])->withOptions([
            'mode' => 'cors',
            'credentials' => 'include',
        ])->get($url);

        if ($response->successful()) {
            return $response->json();
        }

        // Handle error appropriately
        throw new \Exception('Failed to fetch data from Subsplash API');
    }

    public function show($slug)
    {

        if (Cache::has($slug)) {
            $value = Cache::get($slug);

            return response()->json($value);
        }

        $responseData = [];

        $series = Entry::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $responseData['id'] = $series->id;
        $responseData['title'] = $series->title;
        $responseData['slug'] = $series->slug;
        $responseData['uri'] = $series->uri;

        $subsplashSeries = $this->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$series->id}");
        $responseData['subtitle'] = $subsplashSeries['subtitle'];

        $series['subsplash_data'] = $subsplashSeries;

        $relatedMediaItemsUrl = $subsplashSeries['_links']['media-items']['href'];
        $responseData['_embedded'] = $subsplashSeries['_embedded'];

        $subsplashMediaItems = $this->subsplashFetch($relatedMediaItemsUrl);

        $subsplashMediaItemMap = [];

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
        }

        $mediaItemEntries = Entry::query()
            ->where('collection', 'media_item')
            ->where('data->series', 'like', "%{$series->id}")
            ->get();

        $responseData['mediaItems'] = [];

        foreach ($mediaItemEntries as $mediaItemEntry) {

            $responseData['mediaItems'][] = [
                ...$subsplashMediaItemMap[$mediaItemEntry->id],
                'slug' => $mediaItemEntry->slug,
                'uri' => $mediaItemEntry->uri,
            ];
        }
        Cache::put($slug, $responseData, $minutes = 10);

        // Return the view with the merged data
        return response()->json($responseData);
    }
}
