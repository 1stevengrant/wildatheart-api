<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Entries\Entry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class SubsplashService
{
    private $podcastIDs;

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
        $token = 'Bearer ' . $accessToken;

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

    public function findMediaItemByTitle($title)
    {
        dump("Searching for:$title");
        return $this->subsplashFetch("https://core.subsplash.com/media/v1/media-items?filter%5Btitle%5D=*" . urlencode($title) . "*");
    }

    public function getMediaSeries($mediaSeriesId)
    {
        return $this->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$mediaSeriesId}");
    }

    public function getMediaSeriesFromItemId($mediaItemId)
    {

        $mediaItem = $this->getMediaItem($mediaItemId);

        return Arr::get($mediaItem, '_embedded.media-series.id', null);
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

    public function saveMediaItemAsEntry($mediaItem, $updateSlug = false)
    {
        $mediaItemID = data_get($mediaItem, 'id');
        $mediaItemTitle = data_get($mediaItem, 'title');
        $mediaItemDate = Carbon::parse(data_get($mediaItem, 'date'));
        $mediaItemDateOnly = $mediaItemDate->format('Y-m-d');
        $mediaItemSeriesID = data_get($mediaItem, '_embedded.media-series.id');

        // Check if the entry already exists
        $entry = Entry::query()
            ->where('collection', 'media_item')
            ->where('id', $mediaItemID)
            ->first();

        // Check if custom media item is already associated to this id.
        $customItem = Entry::query()
            ->where('collection', 'media_item')
            ->where('blueprint', 'custom_media_item')
            ->where('data->subsplash_media_item_id', $mediaItemID)
            ->first();

        // If this is a custom item, return it.
        if ((bool) $customItem) {
            return $customItem;
        }


        // If the entry does not exist, create it
        $entryCreated = false;
        if (! $entry) {
            $entry = Entry::make()
                ->published(true)
                ->blueprint('subsplash_media_item')
                ->collection('media_item')
                ->id(data_get($mediaItem, 'id'))
                ->slug($mediaItemTitle);
            $entryCreated = true;
        }

        $data = $entry->data();
        $data['title'] = $mediaItemTitle;
        $data['subsplash_media_item_id'] = $mediaItemID;

        // If there is an associated series, associate it to the entry.
        if ($mediaItemSeriesID) {

            // Use the series ID to see if this item is a podcast.
            if ($this->isPodcast($mediaItemSeriesID)) {
                $data['media_type'] = ['podcast-episode'];
            }

            $mediaSeries = Entry::query()
                ->where('collection', 'media_series')
                ->where('id', $mediaItemSeriesID)
                ->first();

            if (! $mediaSeries) {
                $mediaSeries = $this->getMediaSeries($mediaItemSeriesID);
                $this->saveMediaSeriesAsEntry($mediaSeries);
            }

            $data['series'] = "entry::$mediaItemSeriesID";
        }


        // Update to uniqe slug if entry is just now being created
        // or if $updateSlug is true.
        if ($entryCreated || $updateSlug) {
            $uniqueSlug = $this->generateUniqueSlug($entry->id, $mediaItemTitle, $mediaItemDateOnly, $mediaItemSeriesID);
            $entry->slug($uniqueSlug);
        }

        $entry->date($mediaItemDate)
            ->data($data)
            ->save();

        return $entry;
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

    /**
     * A helper method that checks if a Series ID belongs to a Podcast.
     *
     * @param  string  $seriesId  the ID of the Subsplash series
     * @return bool True if series id is a podcast
     */
    private function isPodcast(string $seriesId): bool
    {

        if (! $this->podcastIDs) {
            $this->podcastIDs = Entry::query()
                ->where('collection', 'podcasts')
                ->get()
                ->pluck('subsplash_series_id');
        }

        return $this->podcastIDs->contains($seriesId);
    }

    /**
     * A helper method that provides a slug that hasn't been used yet.
     *
     * Note: This method only works for Media Item entries as they have been
     * configured to use their associated series as part of their uri meaning
     * it is possible for items to have the same slug and not overlap.
     *
     * @param  string  $entryId  The id of the entry this is working with.
     * @param  string  $title  The title to slugify.
     * @param  string  $fallback  The fallback text to add if the slugified title is already in use.
     * @param  ?string  $mediaItemSeriesID  For entries that incorporate their associated series into
     *                                      their uri, the id associated to their series
     * @return string A slug that is not in use yet.
     */
    public function generateUniqueSlug(
        string $entryId,
        string $title,
        string $fallback,
        ?string $mediaItemSeriesID
    ) {
        $slugifiedTitle = Str::slug($title);

        $query = Entry::query()
            ->where('collection', 'media_item')
            ->where('slug', $slugifiedTitle);

        if ($mediaItemSeriesID) {
            $query->where('series', "entry::$mediaItemSeriesID");
        }

        $result = $query->first();

        if (! $result || $result->id == $entryId) {
            return $slugifiedTitle;
        }

        return Str::slug("$title-$fallback");
    }
}
