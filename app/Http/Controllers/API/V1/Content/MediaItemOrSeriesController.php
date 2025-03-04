<?php

namespace App\Http\Controllers\API\V1\Content;

use Statamic\Entries\Entry;
use Statamic\Facades\Token;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Facades\Statamic\CP\LivePreview;
use App\Services\BlockBuilderService;
use App\Services\EntryBuilderService;
use App\Services\ImageBuilderService;
use App\DataTypeObjects\MediaItemData;
use App\DataTypeObjects\API\V1\ErrorResponse;
use App\DataTypeObjects\API\V1\RedirectResponse;
use App\Http\Controllers\API\MediaItemDataController;
use App\Http\Controllers\API\MediaSeriesDataController;
use App\DataTypeObjects\Statamic\Blueprints\CustomSeries;
use App\DataTypeObjects\Statamic\Blueprints\SubsplashMediaSeries;

class MediaItemOrSeriesController extends Controller
{
    private BlockBuilderService $blockBuilder;

    public function __construct(BlockBuilderService $blockBuilder)
    {
        $this->blockBuilder = $blockBuilder;
    }

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

        // Check for token and live-preview query parameters
        $token = $request->query('token', false);
        $livePreview = $request->query('live-preview', false);

        // If token is present or live-preview is true, adjust the query to include unpublished and future items

        $mediaItemOrSeries = null;

        if ($token && $livePreview) {
            // Skip published and date checks for live preview or token access

            $token = Token::find($token);
            if ($token) {
                $mediaItemOrSeries = LivePreview::item($token);
            }
        } else {
            $mediaItemOrSeries = Entry::query()
                ->where(function ($query) {
                    $query->where('collection', 'media_item')
                        ->orWhere('collection', 'media_series');
                })
                ->where(function ($query) use ($slug, $uri) {
                    $query->where('slug', $slug)
                        ->orWhere('uri', $uri);
                })
                ->where('published', true)
                ->where(function ($query) {
                    $query->where('date', '<=', now())
                        ->orWhere('data->make_scheduled_page_public', true);
                })
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

        $entryBuilder = new EntryBuilderService;

        $response = $entryBuilder->buildResponseFromEntry($mediaItemOrSeries);

        if (isset($mediaItemOrSeries->blocks)) {
            $response['blocks'] = $this->blockBuilder->build($mediaItemOrSeries->blocks);
        }

        $imageBuilder = new ImageBuilderService;

        $response['images'] = $imageBuilder->build($mediaItemOrSeries->images);

        if ($response['blueprint']['handle'] === 'custom_media_item') {
            return MediaItemData::from($response);
        }

        // Don't get related media items if series isn't defined.
        if (!!$mediaItemOrSeries->series) {
            $mediaItemsInSeries = Entry::query()
                ->where('collection', 'media_item')
                ->where('data->series', 'like', "%{$mediaItemOrSeries->series}")
                ->get();

            // $mediaItem = $mediaItem->toArray();
            $response['mediaItems'] = $mediaItemsInSeries->toArray();
        }

        if ($response['blueprint']['handle'] === 'custom_series') {
            return CustomSeries::from($response);
        }

        return SubsplashMediaSeries::from($response);
    }

    public function redirect($uri) {}
}
