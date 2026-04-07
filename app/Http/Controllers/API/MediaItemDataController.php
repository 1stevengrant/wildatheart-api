<?php

namespace App\Http\Controllers\API;

use Statamic\Entries\Entry;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use Statamic\Eloquent\Entries\UuidEntryModel;
use App\Services\Subsplash\SubsplashApiClient;
use App\Services\Subsplash\SubsplashMediaSeriesImporter;

class MediaItemDataController extends Controller
{
    public function __construct(
        private SubsplashApiClient $apiClient,
        private SubsplashMediaSeriesImporter $seriesImporter,
    ) {}

    public function mediaBySeriesId($seriesId)
    {
        $mediaItemsInSeries = EntryQueryBuilder::mediaItems()
            ->inSeries($seriesId)
            ->orderBy('data->position', 'asc')
            ->get();

        return response()->json($mediaItemsInSeries);
    }

    public function media($slug)
    {
        $mediaItem = EntryQueryBuilder::mediaItems()
            ->bySlug($slug)
            ->firstOrFail();

        $mediaItemsInSeries = EntryQueryBuilder::mediaItems()
            ->inSeries($mediaItem->series)
            ->get();

        $mediaItem['mediaItems'] = $mediaItemsInSeries->toArray();

        return response()->json($mediaItem);
    }

    public function subsplashMedia($slug)
    {
        $mediaItem = EntryQueryBuilder::mediaItems()
            ->bySlug($slug)
            ->firstOrFail();

        $responseData = $this->buildSubsplashMediaItemResponse($mediaItem);

        return response()->json($responseData);
    }

    public function buildSubsplashMediaItemResponse(Entry|UuidEntryModel $mediaItem)
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


        // Check if Vimeo ID exists and use it instead of Subsplash
        if ($mediaItem->vimeo_id) {
            // Handle unlisted Vimeo videos that have a format like "1067873475/63a07f9357"
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

        // Handle scenario where Subsplash API isn't returning subtitle
        if (isset($subsplashMediaItem['subtitle'])) {
            $responseData['subtitle'] = $subsplashMediaItem['subtitle'];
        }

        // Handle scenario where Subsplash API isn't returning summary
        if (isset($subsplashMediaItem['summary'])) {
            $responseData['summary'] = $subsplashMediaItem['summary'];
        }

        // Handle scenario where Subsplash API isn't returning summary_text
        if (isset($subsplashMediaItem['summary_text'])) {
            $responseData['summary_text'] = $subsplashMediaItem['summary_text'];
        }

        // If we're set to override we should use the cms description
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
                $mediaItemEntry = $this->apiClient->getMediaItem(data_get($mediaItem, 'id'));
            }

            $mediaItemEntries = EntryQueryBuilder::mediaItems()
                ->inSeries($seriesEntry->id)
                ->published()
                ->pastPublishDate()
                ->recentFirst()
                ->limit(20)
                ->get();

            $responseData['mediaItems'] = [];

            foreach ($mediaItemEntries as $mediaItemEntry) {

                $mediaItemId = $mediaItemEntry->data()->get('subsplash_media_item_id');
                // Handle scenario where Subsplash API isn't returning accurately
                if (! isset($subsplashMediaItemMap[$mediaItemId])) {
                    $mediaItem = $this->apiClient->getMediaItem($mediaItemId);
                    $subsplashMediaItemMap[$mediaItemId] = $mediaItem;
                }

                $subsplashMediaItem = $this->apiClient->getMediaItem($mediaItemEntry->subsplash_media_item_id);
                $responseData['mediaItems'][] = [
                    ...$subsplashMediaItem,
                    'slug' => $mediaItemEntry->slug,
                    'uri' => $mediaItemEntry->uri,
                ];
            }
        }

        return $responseData;
    }

    public function getRedirects()
    {
        $mediaItem = EntryQueryBuilder::mediaItems()
            ->get();

        $output = '';
        foreach ($mediaItem as $item) {
            if ($item->drupal_url) {
                $output .= "{$item->drupal_url} {$item->uri} 301\n";
            }
        }

        return response($output)->header('Content-Type', 'text/plain');
    }
}
