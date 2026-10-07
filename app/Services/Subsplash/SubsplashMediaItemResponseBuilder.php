<?php

namespace App\Services\Subsplash;

use Statamic\Entries\Entry;
use Illuminate\Http\JsonResponse;
use App\Builders\EntryQueryBuilder;
use Statamic\Eloquent\Entries\UuidEntryModel;

class SubsplashMediaItemResponseBuilder
{
    public function __construct(
        private readonly SubsplashApiClient $apiClient,
        private readonly SubsplashMediaSeriesImporter $seriesImporter,
    ) {}

    public function build(Entry|UuidEntryModel $mediaItem): array|JsonResponse
    {
        $responseData = [];

        $mediaItemId = $mediaItem->data()->get('subsplash_media_item_id');

        $responseData['id'] = $mediaItemId;
        $responseData['slug'] = $mediaItem->slug;
        $responseData['uri'] = $mediaItem->uri;
        $responseData['blueprint'] = $mediaItem->blueprint;

        $subsplashMediaItem = $this->apiClient->getMediaItem($mediaItemId);

        $responseData['date'] = data_get($subsplashMediaItem, 'date');

        if (isset($subsplashMediaItem['errors']) && $subsplashMediaItem['errors'][0]['code'] === 'resource_not_found') {
            return response()->json($mediaItem);
        }

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
            $relatedMediaItemsUrl = $subsplashSeries['_links']['media-items']['href'];

            $subsplashMediaItems = $this->apiClient->fetch($relatedMediaItemsUrl . '&filter[status]=published');

            $subsplashMediaItemMap = [];

            foreach ($subsplashMediaItems['_embedded']['media-items'] as $mediaItem) {
                $subsplashMediaItemMap[$mediaItem['id']] = $mediaItem;
                $this->apiClient->getMediaItem(data_get($mediaItem, 'id'));
            }

            $mediaItemEntries = EntryQueryBuilder::mediaItems()
                ->inSeries($seriesId)
                ->published()
                ->pastPublishDate()
                ->recentFirst()
                ->limit(20)
                ->get();

            $responseData['mediaItems'] = [];

            foreach ($mediaItemEntries as $mediaItemEntry) {
                $mediaItemId = $mediaItemEntry->data()->get('subsplash_media_item_id');
                if (! isset($subsplashMediaItemMap[$mediaItemId])) {
                    $mediaItem = $this->apiClient->getMediaItem($mediaItemId);
                    if ($mediaItem !== null) {
                        $subsplashMediaItemMap[$mediaItemId] = $mediaItem;
                    }
                }

                $subsplashMediaItem = $this->apiClient->getMediaItem($mediaItemEntry->subsplash_media_item_id);
                if ($subsplashMediaItem === null) {
                    continue;
                }

                $responseData['mediaItems'][] = [
                    ...$subsplashMediaItem,
                    'slug' => $mediaItemEntry->slug,
                    'uri' => $mediaItemEntry->uri,
                ];
            }
        }

        return $responseData;
    }
}
