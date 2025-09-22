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

        $response = Http::retry([100, 500, 1000])->withHeaders([
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
        throw new \Exception("Request to Subsplash API failed with {$response->status()} after three attempts: $url");
    }

    public function getMediaItem($mediaItemId)
    {
        return $this->subsplashFetch("https://core.subsplash.com/media/v1/media-items/{$mediaItemId}");
    }

    public function findMediaItemByTitle($title)
    {
        dump("Searching for:$title");

        return $this->subsplashFetch('https://core.subsplash.com/media/v1/media-items?filter%5Btitle%5D=*' . urlencode($title) . '*');
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
        $mediaItemSummary = data_get($mediaItem, 'summary');
        $mediaItemDescription = data_get($mediaItem, 'description');
        $mediaItemSubtitle = data_get($mediaItem, 'subtitle');
        $mediaItemSpeaker = data_get($mediaItem, 'speaker');
        $mediaItemDurationMilliseconds = data_get($mediaItem, '_embedded.video.duration');
        $mediaItemDuration = $mediaItemDurationMilliseconds ? gmdate('H:i:s', $mediaItemDurationMilliseconds / 1000) : null;
        $shortCode = data_get($mediaItem, 'short_code');
        $iFrameUrl = "https://subsplash.com/+f83c/embed/mi/+{$shortCode}?audio&video&logoWatermark";

        $audioDownloadUrl = data_get($mediaItem, '_embedded.audio._embedded.audio-outputs.0._links.download.href');
        $audioFileName = data_get($mediaItem, '_embedded.audio.title', 'audio.mp3');

        if ($audioDownloadUrl) {
            $audioDownloadUrl = str_replace('{filename}.mp3', $audioFileName, $audioDownloadUrl);
        }


        $videoDownloadUrl = data_get($mediaItem, '_embedded.video._embedded.video-outputs.0._links.related.href');
        $videoFileName = data_get($mediaItem, '_embedded.video.title', 'video.mp4');

        if ($videoDownloadUrl) {
            $videoDownloadUrl = str_replace('{filename}.mp4', $videoFileName, $videoDownloadUrl);
        }

        $images = data_get($mediaItem, '_embedded.images');
        $wideImageUrl = data_get(collect($images)->where('type', 'wide')->first(), "_links.dynamic.href");
        $squareImageUrl = data_get(collect($images)->where('type', 'square')->first(), "_links.dynamic.href");
        $bannerImageUrl = data_get(collect($images)->where('type', 'banner')->first(), "_links.dynamic.href");


        if ($wideImageUrl) {
            $wideImageUrl = str_replace(['{name}', '{ext}', '{width}', '{height}'], ['image', 'jpg', '800', '450'], $wideImageUrl);
        }
        if ($squareImageUrl) {
            $squareImageUrl = str_replace(['{name}', '{ext}', '{width}', '{height}'], ['image', 'jpg', '800', '800'], $squareImageUrl);
        }
        if ($bannerImageUrl) {
            $bannerImageUrl = str_replace(['{name}', '{ext}', '{width}', '{height}'], ['image', 'jpg', '1920', '692'], $bannerImageUrl);
        }

        if (!$mediaItemDuration) {
            $mediaItemDuration = data_get($mediaItem, '_embedded.audio._embedded.audio-outputs.0.duration', 0);
        }

        $mediaItemDate = Carbon::parse(data_get($mediaItem, 'date'));
        $mediaItemDateOnly = $mediaItemDate->format('Y-m-d');
        $mediaItemSeriesID = data_get($mediaItem, '_embedded.media-series.id');

        // Check if the entry already exists
        $entry = Entry::query()
            ->where('collection', 'media_item')
            ->where('id', $mediaItemID)
            ->orWhere('data->subsplash_media_item_id', $mediaItemID)
            ->first();

        // If the entry exists and the override subsplash content toggle is set, don't update it.
        if ($entry && $entry->data()->get('override_subsplash_content')) {
            return $entry;
        }

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
                ->date($mediaItemDate)
                ->slug($mediaItemTitle);

            $entryCreated = true;
        }

        $subsplashData = [];
        $subsplashData['speaker'] = $mediaItemSpeaker;
        $subsplashData['duration'] = $mediaItemDuration;
        $subsplashData['wide_image_url'] = $wideImageUrl;
        $subsplashData['square_image_url'] = $squareImageUrl;
        $subsplashData['banner_image_url'] = $bannerImageUrl;
        $data = $entry->data();
        $data['last_imported'] = Carbon::now();
        $data['last_updated'] = Carbon::parse(data_get($mediaItem, 'updated_at'));
        $data['title_subsplash'] = $mediaItemTitle;
        $data['subtitle_subsplash'] = $mediaItemSubtitle;
        $data['description_subsplash'] = $mediaItemSummary;
        $data['date_subsplash'] = $mediaItemDateOnly;
        $data['speaker_subsplash'] = $mediaItemSpeaker;
        $data['video_iframe_url_subsplash'] = $iFrameUrl;
        $data['video_direct_url_subsplash'] = $videoDownloadUrl;
        $data['audio_iframe_url_subsplash'] = $iFrameUrl;
        $data['audio_direct_url_subsplash'] = $audioDownloadUrl;
        $data['duration_subsplash'] = $mediaItemDuration;
        $data['images_subsplash'] = collect($images)->map(function ($image) {
            // Import image to our library if not already imported
            $sourceUrl = $image['_links']['related']['href'] ?? null;
            $imageAssetId = null;

            if ($sourceUrl) {
                $statamicAsset = $this->saveImageAsAsset($image);
            }

            return [
                "id" => $image['id'],
                "type" => "new_image",
                "enabled" => true,
                "image_id_subsplash" => $image['id'],
                "image_type_subsplash" => $image['type'],
                "image_source_url_subsplash" => $image['_links']['related']['href'],
                "image_dynamic_url_subsplash" => $image['_links']['dynamic']['href'],
                "statamic_asset_path" => $statamicAsset->path(),
            ];
        })->toArray();


        // $data['topics_subsplash'] = $mediaItemTopics;
        // $data['video_iframe_url_subsplash'] = $mediaItemVideoIframeUrl;
        // $data['video_direct_url_subsplash'] = $mediaItemVideoDirectUrl;
        // $data['audio_iframe_url_subsplash'] = $mediaItemAudioIframeUrl;
        $data['title'] = $mediaItemTitle;
        $data['speaker'] = $mediaItemSpeaker;
        $data['subtitle'] = $mediaItemSubtitle;
        $data['subsplash_media_item_id'] = $mediaItemID;
        $data['description'] = $mediaItemSummary;
        $data['visible_date'] = $mediaItemDateOnly;
        $data['video_iframe_url'] = $iFrameUrl;
        $data['audio_direct_url'] = $audioDownloadUrl;
        $data['audio_iframe_url'] = $iFrameUrl;
        $data['video_direct_url'] = $videoDownloadUrl;

        $images = [];
        foreach ($data['images_subsplash'] as $image) {
            $images[$image['image_type_subsplash']] = $image['statamic_asset_path'];
        }
        $data['images'] = $images;



        if (!isset($data['subsplash_data'])) {
            $data['subsplash_data'] = $subsplashData;
        }

        // If there is an associated series, associate it to the entry.
        if ($mediaItemSeriesID) {

            // Use the series ID to see if this item is a podcast.
            if ($this->isPodcast($mediaItemSeriesID)) {
                $data['media_type'] = ['podcast-episode'];
            }

            $mediaSeries = $this->getSeriesInStatamic($mediaItemSeriesID);

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

        $entry
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

        return $entry;
    }

    public function saveImageAsAsset($image)
    {
        // Check if we already have this image imported by looking for existing asset
        $existingAsset = \Statamic\Assets\Asset::query()
            ->where('subsplash_image_id', $image['id'])
            ->first();
        $sourceUrl = $image['_links']['related']['href'];

        if (!$existingAsset) {
            try {
                // Download and import the image
                $imageContent = file_get_contents($sourceUrl);

                if ($imageContent !== false) {
                    $filename = 'subsplash_' . $image['id'] . '_' . time() . '.jpg';
                    $path = 'subsplash-imports/' . $filename;

                    $container = \Statamic\Facades\AssetContainer::all()->where('handle', 's3')->first();


                    if ($container) {
                        // Save the file to the container
                        $container->disk()->put($path, $imageContent);

                        // Create the asset entry
                        $asset = $container->makeAsset($path);
                        $asset->data([
                            'subsplash_image_id' => $image['id'],
                            'subsplash_image_type' => $image['type'],
                            'imported_at' => now(),
                        ]);
                        $asset->save();

                        $existingAsset = $asset;
                    }
                }
            } catch (\Exception $e) {
                // Log error but continue processing
                \Illuminate\Support\Facades\Log::warning('Failed to import Subsplash image: ' . $e->getMessage(), [
                    'image_id' => $image['id'],
                    'source_url' => $sourceUrl
                ]);
            }
        }

        return $existingAsset;
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

    public function getSeriesInStatamic(string $seriesId): ?Entry
    {
        return Entry::query()
            ->where('collection', 'media_series')
            ->where('id', $seriesId)
            ->orWhere(function ($query) use ($seriesId) {
                $query->where('blueprint', 'custom_series')
                    ->where('subsplash_media_series_id', $seriesId);
            })
            ->orWhere(function ($query) use ($seriesId) {
                $query->where('collection', 'experiences')
                    ->where('subsplash_series_id', $seriesId);
            })
            ->first();
    }
}
