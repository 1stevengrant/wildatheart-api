<?php

namespace App\Http\Controllers\API\V1\Content;

use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\BlockBuilderService;
use App\Services\EntryBuilderService;
use App\DataTypeObjects\MediaItemData;
use App\Http\Controllers\API\MediaItemDataController;
use App\Http\Controllers\API\V1\Content\Concerns\ResolvesLivePreview;

class MediaItemEntryController extends Controller
{
    use ResolvesLivePreview;

    public function __construct(
        private readonly EntryBuilderService $entryBuilder,
        private readonly BlockBuilderService $blockBuilder,
    ) {}

    public function media($slug)
    {
        $mediaItem = $this->resolveLivePreview();

        if (! $mediaItem) {
            $mediaItem = EntryQueryBuilder::mediaItems()
                ->published()
                ->bySlug($slug)
                ->visibleOrScheduled()
                ->first();
        }

        if (! $mediaItem) {
            return response()->json(['error' => 'Media not found'], 404);
        }

        // Adding this as workaround until I can refactor into a service
        if ($mediaItem->blueprint->handle === 'subsplash_media_item') {
            return app(MediaItemDataController::class)->buildSubsplashMediaItemResponse($mediaItem);
        }

        $response = $this->entryBuilder->buildResponseFromEntry($mediaItem);

        $mediaItemsInSeries = EntryQueryBuilder::mediaItems()
            ->inSeries($mediaItem->series)
            ->get();

        $response['mediaItems'] = $mediaItemsInSeries->toArray();

        if (isset($mediaItem->blocks)) {
            $response['blocks'] = $this->blockBuilder->build($mediaItem->blocks);
        }

        $response['images'] = $mediaItem->images;

        return MediaItemData::from($response);
    }
}
