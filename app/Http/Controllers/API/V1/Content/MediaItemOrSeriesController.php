<?php

namespace App\Http\Controllers\API\V1\Content;

use Illuminate\Http\Request;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\BlockBuilderService;
use App\Services\EntryBuilderService;
use App\DataTypeObjects\MediaItemData;
use App\DataTypeObjects\API\V1\ErrorResponse;
use App\DataTypeObjects\API\V1\RedirectResponse;
use App\Http\Controllers\API\MediaItemDataController;
use App\Http\Controllers\API\MediaSeriesDataController;
use App\DataTypeObjects\Statamic\Blueprints\CustomSeries;
use App\DataTypeObjects\Statamic\Blueprints\SubsplashMediaSeries;
use App\Http\Controllers\API\V1\Content\Concerns\ResolvesLivePreview;

class MediaItemOrSeriesController extends Controller
{
    use ResolvesLivePreview;

    public function __construct(
        private readonly BlockBuilderService $blockBuilder,
        private readonly EntryBuilderService $entryBuilder,
    ) {}

    public function show($slugOrUri, Request $request)
    {
        $uri = $slugOrUri;
        $slug = $slugOrUri;

        if (str_contains($slugOrUri, '/')) {
            $uri = '/media/' . $slugOrUri;
        }

        $seriesRedirects = [
            'the-series-official-trailer',
            'prologue-john',
            'chapter-1-mark',
            'chapter-2-pablo',
            'chapter-3-leo',
            'chapter-4-sam',
        ];

        if (config('features.is_custom_uri_enabled') && in_array($slug, $seriesRedirects)) {
            return RedirectResponse::from([
                'redirect' => '/media/the-series/' . $slug,
                'code' => 301,
            ]);
        }

        $mediaItemOrSeries = $this->resolveLivePreview();

        if (! $mediaItemOrSeries) {
            $mediaItemOrSeries = EntryQueryBuilder::query()
                ->where(function ($query) {
                    $query->where('collection', 'media_item')
                        ->orWhere('collection', 'media_series');
                })
                ->where(function ($query) use ($slug, $uri) {
                    $query->where('slug', $slug)
                        ->orWhere('uri', $uri);
                })
                ->published()
                ->visibleOrScheduled()
                ->first();
        }

        if (! $mediaItemOrSeries) {
            return ErrorResponse::from(['error' => 'Media not found']);
        }

        if ($mediaItemOrSeries->blueprint->handle === 'subsplash_media_item') {
            return app(MediaItemDataController::class)->buildSubsplashMediaItemResponse($mediaItemOrSeries);
        }

        if ($mediaItemOrSeries->blueprint->handle === 'subsplash_media_series') {
            return app(MediaSeriesDataController::class)->buildSubsplashMediaSeriesResponse($mediaItemOrSeries);
        }

        $response = $this->entryBuilder->buildResponseFromEntry($mediaItemOrSeries);

        if (isset($mediaItemOrSeries->blocks)) {
            $response['blocks'] = $this->blockBuilder->build($mediaItemOrSeries->blocks);
        }

        $response['images'] = $mediaItemOrSeries->images;

        if ($response['blueprint']['handle'] === 'custom_media_item') {
            return MediaItemData::from($response);
        }

        // Don't get related media items if series isn't defined.
        if ((bool) $mediaItemOrSeries->series) {
            $mediaItemsInSeries = EntryQueryBuilder::mediaItems()
                ->inSeries($mediaItemOrSeries->series)
                ->limit(15)
                ->get();

            $response['mediaItems'] = $mediaItemsInSeries->toArray();
        }

        if ($response['blueprint']['handle'] === 'custom_series') {
            return CustomSeries::from($response);
        }

        return SubsplashMediaSeries::from($response);
    }

    public function redirect($uri) {}
}
