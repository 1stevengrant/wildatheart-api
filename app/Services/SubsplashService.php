<?php

namespace App\Services;

use Carbon\Carbon;
use Statamic\Entries\Entry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SubsplashService
{
    public function getAccessToken()
    {

        $SUBSPLASH_CLIENT_ID = config('services.subsplash.client_id');
        $SUBSPLASH_CLIENT_SECRET = config('services.subsplash.client_secret');
        $SUBSPLASH_SCOPE = config('services.subsplash.scope');
        $SUBSPLASH_AUTHENTICATION_TOKEN_URL = config('services.subsplash.authentication_token_url');

        if (Cache::has($SUBSPLASH_CLIENT_ID)) {
            $value = Cache::get($SUBSPLASH_CLIENT_ID);

            return $value;
        }

        $response = Http::asForm()->post($SUBSPLASH_AUTHENTICATION_TOKEN_URL, [
            'grant_type' => 'client_credentials',
            'client_id' => $SUBSPLASH_CLIENT_ID,
            'client_secret' => $SUBSPLASH_CLIENT_SECRET,
            'scope' => "app:{$SUBSPLASH_SCOPE}",
        ]);

        if ($response->successful()) {
            $data = $response->json();
            Cache::put($SUBSPLASH_CLIENT_ID, $data['access_token'], $data['expires_in']);

            return $data['access_token'];
        }

        throw new \Exception('Failed to fetch access token');
    }

    public function subsplashFetch($url)
    {
        $SUBSPLASH_CACHE_DURATION = config('services.subsplash.cache_duration');

        $cacheKey = md5($url);
        if (Cache::has($cacheKey)) {
            $value = Cache::get($cacheKey);

            return $value;
        }

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
            $json = $response->json();
            Cache::put($cacheKey, $json, (int) $SUBSPLASH_CACHE_DURATION);

            return $json;
        }

        if (isset($response->json()['errors'])) {
            return $response->json();
        }

        // Handle error appropriately
        throw new \Exception('Failed to fetch data from Subsplash API');
    }

    public function getMediaItem($mediaItemId)
    {
        return $this->subsplashFetch("https://core.subsplash.com/media/v1/media-items/{$mediaItemId}");
    }

    public function getMediaSeries($mediaSeriesId)
    {
        return $this->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$mediaSeriesId}");
    }

    public function getRelatedMediaItems($seriesId)
    {
        if (! $seriesId) {
            return null;
        }

        $subsplashSeries = $this->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$seriesId}");

        $relatedMediaItemsUrl = $subsplashSeries['_links']['media-items']['href'];

        $subsplashMediaItems = $this->subsplashFetch($relatedMediaItemsUrl);

        return $subsplashMediaItems['_embedded']['media-items'];
    }

    public function getAllRelatedMediaItems($seriesId)
    {
        if (! $seriesId) {
            return null;
        }

        $subsplashSeries = $this->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$seriesId}");

        $relatedMediaItemsUrl = $subsplashSeries['_links']['media-items']['href'];

        $subsplashMediaItems = $this->subsplashFetch($relatedMediaItemsUrl);
        $next = data_get($subsplashMediaItems, '_links.next.href');
        $mediaItems = data_get($subsplashMediaItems, '_embedded.media-items', []);

        while ($next) {
            $subsplashMediaItems = $this->subsplashFetch($next);
            $next = data_get($subsplashMediaItems, '_links.next.href');
            $mediaItems = array_merge($mediaItems, data_get($subsplashMediaItems, '_embedded.media-items', []));
        }

        return $mediaItems;
    }

    public function saveMediaItemAsEntry($mediaItem)
    {
        // Check if the entry already exists
        $entry = Entry::query()
            ->where('collection', 'media_item')
            ->where('id', data_get($mediaItem, 'id'))
            ->first();

        // If the entry does not exist, create it
        if (! $entry) {
            $entry = Entry::make()
                ->published(true)
                ->blueprint('subsplash_media_item')
                ->collection('media_item')
                ->id(data_get($mediaItem, 'id'))
                ->slug(data_get($mediaItem, 'title'));
        }

        $publishedAt = data_get($mediaItem, 'published_at');
        $entry->date(\Carbon\Carbon::parse($publishedAt));

        $entry->slug(data_get($mediaItem, 'title'));

        $data = [
            'title' => data_get($mediaItem, 'title'),
            'subsplash_media_item_id' => data_get($mediaItem, 'id'),
        ];

        $seriesId = data_get($mediaItem, '_embedded.media-series.id');

        if ($seriesId) {

            $mediaSeries = Entry::query()
                ->where('collection', 'media_series')
                ->where('id', $seriesId)
                ->first();

            if (! $mediaSeries) {
                $mediaSeries = $this->getMediaSeries($seriesId);
                $this->saveMediaSeriesAsEntry($mediaSeries);
            }

            $data['series'] = "entry::$seriesId";
        }

        $entry->data($data);
        $entry->save();
    }

    public function saveMediaSeriesAsEntry($mediaSeries)
    {
        if (! $mediaSeries['id']) {
            return;
        }

        $entry = Entry::query()
            ->where('collection', 'media_series')
            ->where('id', $mediaSeries['id'])
            ->orWhereJsonContains('data->subsplash_media_series_id', $mediaSeries['id'])
            ->first();

        if ($entry && $entry->subsplash_media_series_id != $entry->id) {

            $duplicate = Entry::make()
                ->collection('media_series')
                ->published(true)
                ->slug($entry->slug)
                ->blueprint($entry->blueprint())
                ->id($mediaSeries['id']);

            $duplicate->data($entry->data()->all());
            $duplicate->save();

            $relatedEntries = Entry::query()
                ->where('data', 'like', "%{$entry->id}%")
                ->get();

            foreach ($relatedEntries as $relatedEntry) {
                $data = $relatedEntry->data()->all();
                $data = str_replace($entry->id, $duplicate->id, json_encode($data));
                $data = json_decode($data, true);
                $relatedEntry->data($data);
                $relatedEntry->save();
            }

            $entry->delete();

            return;
        }

        if (! $entry) {
            $entry = Entry::make()
                ->published(true)
                ->blueprint('subsplash_media_series')
                ->collection('media_series')
                ->id($mediaSeries['id'])
                ->slug($mediaSeries['title']);

            $entry->date(Carbon::parse($mediaSeries['published_at']));

            $entry->data([
                'title' => $mediaSeries['title'],
                'subsplash_media_series_id' => $mediaSeries['id'],
            ]);

            $entry->save();
        }
    }
}
