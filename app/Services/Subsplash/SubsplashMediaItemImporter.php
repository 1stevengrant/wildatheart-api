<?php

namespace App\Services\Subsplash;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Statamic\Entries\Entry;
use Illuminate\Support\Collection;
use App\Data\SubsplashMediaItemData;
use Statamic\Facades\Entry as EntryFacade;

class SubsplashMediaItemImporter
{
    private $podcastIDs;

    public function __construct(
        private SubsplashApiClient $apiClient,
        private SubsplashAssetImporter $assetImporter,
        private SubsplashTopicService $topicService,
        private SubsplashMediaSeriesImporter $seriesImporter,
    ) {}

    public function import(array $mediaItem, bool $updateSlug = false, bool $force = false, ?callable $callback = null): Entry
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
        $topicIds = $this->topicService->processFromTags($mediaData->tags);
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

    private function findCustomMediaItem(string $mediaItemId): ?Entry
    {
        return EntryFacade::query()
            ->where('collection', 'media_item')
            ->where('data->subsplash_media_item_id', $mediaItemId)
            ->whereIn('blueprint', ['custom_media_item', 'experience_session'])
            ->first();
    }

    private function findOrCreateMediaItemEntry(SubsplashMediaItemData $mediaData): array
    {
        $entry = EntryFacade::query()
            ->where('collection', 'media_item')
            ->where('id', $mediaData->id)
            ->orWhere('data->subsplash_media_item_id', $mediaData->id)
            ->first();

        $entryCreated = false;

        if (! $entry) {
            $entry = EntryFacade::make()
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

    private function shouldSkipImport(Entry $entry, SubsplashMediaItemData $mediaData, ?callable $callback): bool
    {
        $lastImportedDateString = $entry->data()->get('last_imported');

        if (! $lastImportedDateString || ! $mediaData->updatedAt) {
            return false;
        }

        $lastImported = Carbon::parse($lastImportedDateString);

        if ($lastImported->gte($mediaData->updatedAt)) {
            if ($callback && is_callable($callback)) {
                $callback('skipped', $mediaData->title, $lastImported, $mediaData->updatedAt);
            }

            return true;
        }

        return false;
    }

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
        $data['images_subsplash'] = $this->assetImporter->processImages($mediaData->images);
    }

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

        $images = [];
        foreach ($data['images_subsplash'] as $image) {
            if (! isset($image['statamic_asset_path'])) {

                continue;
            }
            $images[$image['image_type_subsplash']] = $image['statamic_asset_path'];
        }
        $data['images'] = $images;
    }

    private function handleSeriesAssociation(Collection $data, SubsplashMediaItemData $mediaData): void
    {
        if (! $mediaData->seriesId) {
            return;
        }

        if ($this->isPodcast($mediaData->seriesId)) {
            $data['media_type'] = ['podcast-episode'];
        }

        $mediaSeries = $this->seriesImporter->findInStatamic($mediaData->seriesId);

        if (! $mediaSeries) {
            $mediaSeries = $this->apiClient->getMediaSeries($mediaData->seriesId);
            $this->seriesImporter->import($mediaSeries);
        }

        $data['series'] = "entry::$mediaData->seriesId";
    }

    private function isPodcast(string $seriesId): bool
    {
        if (! $this->podcastIDs) {
            $this->podcastIDs = EntryFacade::query()
                ->where('collection', 'podcasts')
                ->get()
                ->pluck('subsplash_series_id');
        }

        return $this->podcastIDs->contains($seriesId);
    }

    private function generateUniqueSlug(
        string $entryId,
        string $title,
        string $fallback,
        ?string $mediaItemSeriesID
    ): string {
        $slugifiedTitle = Str::slug($title);

        $query = EntryFacade::query()
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
