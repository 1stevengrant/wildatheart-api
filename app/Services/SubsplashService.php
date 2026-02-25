<?php

namespace App\Services;

use Carbon\Carbon;
use Statamic\Facades\Term;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Statamic\Entries\Entry;
use Illuminate\Support\Collection;
use App\Data\SubsplashMediaItemData;
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

        $response = Http::retry(
            [100, 500, 1000],
            when: fn ($exception, $request) => $exception->response->status() >= 500,
            throw: false,
        )->withHeaders([
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

        if ($response->notFound()) {
            return null;
        }

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

    public function saveMediaItemAsEntry($mediaItem, $updateSlug = false, $force = false, $callback = null)
    {
        $mediaData = SubsplashMediaItemData::fromSubsplashResponse($mediaItem);

        if ($customItem = $this->findCustomMediaItem($mediaData->id)) {
            return $customItem;
        }

        [$entry, $entryCreated] = $this->findOrCreateMediaItemEntry($mediaData);

        if (! $entryCreated && ! $force && $this->shouldSkipImport($entry, $mediaData, $callback)) {
            return $entry;
        }

        $data = $entry->data();
        $this->populateSubsplashFields($data, $mediaData);
        $topicIds = $this->processTopicsFromTags($mediaData->tags);
        $data['topics_subsplash'] = $topicIds;

        if (! $entry->data()->get('override_subsplash_content')) {
            $this->updateNonOverrideFields($data, $mediaData, $topicIds);
        }

        $this->handleSeriesAssociation($data, $mediaData);

        if ($entryCreated || $updateSlug) {
            $entry->slug($this->generateUniqueSlug($entry->id, $mediaData->title, $mediaData->dateOnly, $mediaData->seriesId));
        }

        $entry->data($data)->save();

        return $entry;
    }

    /**
     * Find a custom media item by Subsplash media item ID
     */
    private function findCustomMediaItem(string $mediaItemId): ?Entry
    {
        return Entry::query()
            ->where('collection', 'media_item')
            ->where('data->subsplash_media_item_id', $mediaItemId)
            ->whereIn('blueprint', ['custom_media_item', 'experience_session'])
            ->first();
    }

    /**
     * Find or create a media item entry
     */
    private function findOrCreateMediaItemEntry(SubsplashMediaItemData $mediaData): array
    {
        $entry = Entry::query()
            ->where('collection', 'media_item')
            ->where('id', $mediaData->id)
            ->orWhere('data->subsplash_media_item_id', $mediaData->id)
            ->first();

        $entryCreated = false;

        if (! $entry) {
            $entry = Entry::make()
                ->published(true)
                ->blueprint('subsplash_media_item')
                ->collection('media_item')
                ->id($mediaData->id)
                ->date($mediaData->date)
                ->slug($mediaData->title);

            $entryCreated = true;
        }

        return [$entry, $entryCreated];
    }

    /**
     * Determine if import should be skipped due to unchanged data
     */
    private function shouldSkipImport(Entry $entry, SubsplashMediaItemData $mediaData, ?callable $callback): bool
    {
        $lastImportedDateString = $entry->data()->get('last_imported');

        if (! $lastImportedDateString || ! $mediaData->updatedAt) {
            return false;
        }

        $lastImported = Carbon::parse($lastImportedDateString);

        // If last_updated from Subsplash is not more recent than our last_updated, skip import
        if ($lastImported->gte($mediaData->updatedAt)) {
            if ($callback && is_callable($callback)) {
                $callback('skipped', $mediaData->title, $lastImported, $mediaData->updatedAt);
            }

            return true;
        }

        return false;
    }

    /**
     * Populate Subsplash-specific fields in the entry data
     */
    private function populateSubsplashFields(Collection $data, SubsplashMediaItemData $mediaData): void
    {
        $data['last_imported'] = Carbon::now();
        $data['last_updated'] = $mediaData->updatedAt;
        $data['title_subsplash'] = $mediaData->title;
        $data['subtitle_subsplash'] = $mediaData->subtitle;
        $data['description_subsplash'] = $mediaData->summary;
        $data['date_subsplash'] = $mediaData->dateOnly;
        $data['speaker_subsplash'] = $mediaData->speaker;
        $data['video_iframe_url_subsplash'] = $mediaData->iframeUrl();
        $data['video_direct_url_subsplash'] = $mediaData->videoDownloadUrl;
        $data['audio_iframe_url_subsplash'] = $mediaData->iframeUrl();
        $data['audio_direct_url_subsplash'] = $mediaData->audioDownloadUrl;
        $data['duration_subsplash'] = $mediaData->duration;
        $data['images_subsplash'] = $this->processSubsplashImages($mediaData->images);
    }

    /**
     * Process Subsplash images and import them as assets
     */
    private function processSubsplashImages(array $images): array
    {
        return collect($images)
            ->filter(fn ($image) => isset($image['_links']['related']['href']))
            ->map(function ($image) {
                $sourceUrl = data_get($image, '_links.related.href');
                $statamicAsset = null;

                if ($sourceUrl) {
                    $statamicAsset = $this->saveImageAsAsset($image);
                }

                $imageData = [
                    'id' => data_get($image, 'id'),
                    'type' => 'new_image',
                    'enabled' => true,
                    'image_id_subsplash' => data_get($image, 'id'),
                    'image_type_subsplash' => data_get($image, 'type'),
                    'image_source_url_subsplash' => data_get($image, '_links.related.href'),
                    'image_dynamic_url_subsplash' => data_get($image, '_links.dynamic.href'),
                ];

                if ($statamicAsset) {
                    $imageData['statamic_asset_path'] = $statamicAsset->path();
                }

                return $imageData;
            })
            ->toArray();
    }

    /**
     * Update fields that should not be overridden by Subsplash content
     */
    private function updateNonOverrideFields(Collection $data, SubsplashMediaItemData $mediaData, array $topicIds): void
    {
        $data['title'] = $mediaData->title;
        $data['speaker'] = $mediaData->speaker;
        $data['subtitle'] = $mediaData->subtitle;
        $data['subsplash_media_item_id'] = $mediaData->id;
        $data['description'] = $mediaData->summary;
        $data['visible_date'] = $mediaData->dateOnly;
        $data['video_iframe_url'] = $mediaData->iframeUrl();
        $data['audio_direct_url'] = $mediaData->audioDownloadUrl;
        $data['audio_iframe_url'] = $mediaData->iframeUrl();
        $data['video_direct_url'] = $mediaData->videoDownloadUrl;
        $data['topics'] = $topicIds;

        // Map images from subsplash data to main images field
        $images = [];
        foreach ($data['images_subsplash'] as $image) {
            if (! isset($image['statamic_asset_path'])) {
                dump($image);

                continue;
            }
            $images[$image['image_type_subsplash']] = $image['statamic_asset_path'];
        }
        $data['images'] = $images;
    }

    /**
     * Handle series association for the media item
     */
    private function handleSeriesAssociation(Collection $data, SubsplashMediaItemData $mediaData): void
    {
        if (! $mediaData->seriesId) {
            return;
        }

        // Use the series ID to see if this item is a podcast
        if ($this->isPodcast($mediaData->seriesId)) {
            $data['media_type'] = ['podcast-episode'];
        }

        $mediaSeries = $this->getSeriesInStatamic($mediaData->seriesId);

        if (! $mediaSeries) {
            $mediaSeries = $this->getMediaSeries($mediaData->seriesId);
            $this->saveMediaSeriesAsEntry($mediaSeries);
        }

        $data['series'] = "entry::{$mediaData->seriesId}";
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
        // Check if we already have this image imported by looking for existing asset by filename
        $filename = 'subsplash_' . $image['id'] . '.jpg';
        $path = 'subsplash-imports/' . $filename;

        $existingAsset = \Statamic\Assets\Asset::query()
            ->where('path', $path)
            ->first();
        $sourceUrl = $image['_links']['related']['href'];

        if (! $existingAsset) {
            try {
                // Download and import the image
                $imageContent = file_get_contents($sourceUrl);

                if ($imageContent !== false) {
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
                    'source_url' => $sourceUrl,
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

    /**
     * Extract topics from Subsplash tags array and create/find topic taxonomy entries
     *
     * @param  array  $tags  The tags array from Subsplash MediaItem
     * @return array Array of topic taxonomy term IDs
     */
    public function processTopicsFromTags(array $tags): array
    {
        $topicIds = [];

        foreach ($tags as $tag) {
            // Only process tags that start with "topic:"
            if (str_starts_with($tag, 'topic:')) {
                $topicName = mb_trim(mb_substr($tag, 6)); // Remove "topic:" prefix

                if (! empty($topicName)) {
                    $topicTerm = $this->createOrFindTopic($topicName);
                    if ($topicTerm) {
                        $topicIds[] = $topicTerm->id();
                    }
                }
            }
        }

        return $topicIds;
    }

    /**
     * Create or find a topic taxonomy term
     *
     * @param  string  $topicName  The name of the topic
     * @return mixed The topic term or null if creation failed
     */
    private function createOrFindTopic(string $topicName)
    {
        // Generate a slug from the topic name
        $slug = Str::slug($topicName);

        // Check if topic already exists
        $existingTerm = Term::query()
            ->where('taxonomy', 'topics')
            ->where('slug', $slug)
            ->first();

        if ($existingTerm) {
            return $existingTerm;
        }

        // Create new topic term
        try {
            $term = Term::make()
                ->taxonomy('topics')
                ->slug($slug)
                ->data([
                    'title' => $topicName,
                ]);

            $term->save();

            return $term;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to create topic term: ' . $e->getMessage(), [
                'topic_name' => $topicName,
                'slug' => $slug,
            ]);

            return null;
        }
    }
}
