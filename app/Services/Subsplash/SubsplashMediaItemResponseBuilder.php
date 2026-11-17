<?php

namespace App\Services\Subsplash;

use Exception;
use Statamic\Entries\Entry;
use App\Builders\EntryQueryBuilder;
use Statamic\Eloquent\Entries\UuidEntryModel;

class SubsplashMediaItemResponseBuilder
{
    public function __construct(
        private readonly SubsplashApiClient $apiClient,
        private readonly SubsplashMediaSeriesImporter $seriesImporter,
    ) {}

    public function build(Entry|UuidEntryModel $mediaItem): array
    {
        $responseData = [];

        $mediaItemId = $mediaItem->data()->get('subsplash_media_item_id');

        $responseData['id'] = $mediaItemId;
        $responseData['slug'] = $mediaItem->slug;
        $responseData['uri'] = $mediaItem->uri;
        $responseData['blueprint'] = $mediaItem->blueprint;

        $subsplashMediaItem = $this->apiClient->getMediaItem($mediaItemId);

        if ($subsplashMediaItem === null || data_get($subsplashMediaItem, 'errors.0.code') === 'resource_not_found') {
            return [
                ...$responseData,
                'title' => $mediaItem->title,
                'date' => $mediaItem->date,
                'summary' => $mediaItem->description,
                'is_vertical_video' => $mediaItem->is_vertical_video,
                'images' => $mediaItem->images,
            ];
        }

        $responseData['date'] = data_get($subsplashMediaItem, 'date');

        $responseData['title'] = $subsplashMediaItem['title'];
        $shortCode = data_get($subsplashMediaItem, 'short_code');

        if ($mediaItem->vimeo_id) {
            if (str_contains($mediaItem->vimeo_id, '/')) {
                [$videoId, $hash] = explode('/', $mediaItem->vimeo_id);
                $responseData['media_iframe_url'] = "https://player.vimeo.com/video/{$videoId}?h={$hash}&badge=0&autopause=0&player_id=0&app_id=58479&autoplay=1";
            } else {
                $responseData['media_iframe_url'] = "https://player.vimeo.com/video/{$mediaItem->vimeo_id}?badge=0&autopause=0&player_id=0&app_id=58479&autoplay=1";
            }
        } else {
            $responseData['media_iframe_url'] = "https://subsplash.com/+f83c/embed/mi/{$shortCode}?audio&video&info&logoWatermark&shareable&embeddable";
        }
        $responseData['is_vertical_video'] = $mediaItem->is_vertical_video;
        $responseData['images'] = $mediaItem->images;

        $responseData['_embedded'] = data_get($subsplashMediaItem, '_embedded');

        if (isset($subsplashMediaItem['position'])) {
            $responseData['position'] = $subsplashMediaItem['position'];
        }

        if (isset($subsplashMediaItem['subtitle'])) {
            $responseData['subtitle'] = $subsplashMediaItem['subtitle'];
        }

        if (isset($subsplashMediaItem['summary'])) {
            $responseData['summary'] = $subsplashMediaItem['summary'];
        }

        if (isset($subsplashMediaItem['summary_text'])) {
            $responseData['summary_text'] = $subsplashMediaItem['summary_text'];
        }

        if ($mediaItem->override_subsplash_content) {
            $responseData['summary_text'] = null;
            $responseData['summary'] = $mediaItem->description;
        }

        $responseData['short_code'] = $subsplashMediaItem['short_code'];

        if (isset($subsplashMediaItem['_embedded']['media-series'])) {
            $subsplashSeries = $subsplashMediaItem['_embedded']['media-series'];
            $seriesId = data_get($subsplashSeries, 'id');
            $responseData['series'] = $subsplashSeries;
            $seriesEntry = $this->seriesImporter->findInStatamic($seriesId);

            $responseData['series']['uri'] = $seriesEntry->uri;
            $responseData['mediaItems'] = $this->seriesMediaItems($seriesId, $subsplashSeries['_links']['media-items']['href']);
        }

        return $responseData;
    }

    /** @return array<int, array<string, mixed>> */
    private function seriesMediaItems(string $seriesId, string $relatedMediaItemsUrl): array
    {
        try {
            $publishedMediaItems = $this->apiClient->fetch("{$relatedMediaItemsUrl}&filter[status]=published");
        } catch (Exception $exception) {
            report($exception);

            return [];
        }

        $subsplashMediaItems = collect(data_get($publishedMediaItems, '_embedded.media-items', []))->keyBy('id');

        $mediaItemEntries = EntryQueryBuilder::mediaItems()
            ->inSeries($seriesId)
            ->published()
            ->pastPublishDate()
            ->recentFirst()
            ->limit(20)
            ->get();

        $mediaItems = [];

        foreach ($mediaItemEntries as $mediaItemEntry) {
            $mediaItemId = $mediaItemEntry->subsplash_media_item_id;

            try {
                $subsplashMediaItem = $subsplashMediaItems->get($mediaItemId) ?? $this->apiClient->getMediaItem($mediaItemId);
            } catch (Exception $exception) {
                report($exception);

                continue;
            }

            if ($subsplashMediaItem === null) {
                continue;
            }

            $mediaItems[] = [
                ...$subsplashMediaItem,
                'slug' => $mediaItemEntry->slug,
                'uri' => $mediaItemEntry->uri,
            ];
        }

        return $mediaItems;
    }
}
