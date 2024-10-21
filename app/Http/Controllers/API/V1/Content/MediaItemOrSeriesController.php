<?php

namespace App\Http\Controllers\API\V1\Content;

use App\DataTypeObjects\MediaItemData;
use App\Http\Controllers\API\MediaItemDataController;
use App\Http\Controllers\API\MediaSeriesDataController;
use App\Http\Controllers\Controller;
use App\Services\BlockBuilderService;
use App\Services\EntryBuilderService;
use App\Services\ImageBuilderService;
use Facades\Statamic\CP\LivePreview;
use Statamic\Entries\Entry;
use Statamic\Facades\Token;

class MediaItemOrSeriesController extends Controller
{
    public function show($slugOrUri)
    {
        $uri = $slugOrUri;
        $slug = $slugOrUri;

        if (str_contains($slugOrUri, '/')) {
            $uri = '/media/' . $slugOrUri;
        } else {
            $slug = $slugOrUri;
        }


        // Check for token and live-preview query parameters
        $request = request();
        $token = $request->query('token', false);
        $livePreview = $request->query('live-preview', false);

        // If token is present or live-preview is true, adjust the query to include unpublished and future items

        $mediaItemOrSeries = null;

        if ($token && $livePreview) {
            // Skip published and date checks for live preview or token access

            $token = Token::find($token);
            $mediaItemOrSeries = LivePreview::item($token);
        } else {
            $mediaItemOrSeries = Entry::query()
                ->where(function ($query) use ($slug, $uri) {
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
            return response()->json(['error' => 'Media not found'], 404);
        }

        // Adding this as workaround until I can refactor into a service
        if ($mediaItemOrSeries->blueprint->handle === 'subsplash_media_item') {
            return app(MediaItemDataController::class)->buildSubsplashMediaItemResponse($mediaItemOrSeries);
        }

        if ($mediaItemOrSeries->blueprint->handle === 'subsplash_media_series') {
            return app(MediaSeriesDataController::class)->buildSubsplashMediaSeriesResponse($mediaItemOrSeries);
        }

        $entryBuilder = new EntryBuilderService;

        $response = $entryBuilder->buildResponseFromEntry($mediaItemOrSeries);

        $mediaItemsInSeries = Entry::query()
            ->where('collection', 'media_item')
            ->where('data->series', 'like', "%{$mediaItemOrSeries->series}")
            ->get();

        // $mediaItem = $mediaItem->toArray();
        $response['mediaItems'] = $mediaItemsInSeries->toArray();

        if (isset($mediaItemOrSeries->blocks)) {
            $blockBuilder = new BlockBuilderService;
            $response['blocks'] = $blockBuilder->build($mediaItemOrSeries->blocks);
        }

        $imageBuilder = new ImageBuilderService;

        $response['images'] = $imageBuilder->build($mediaItemOrSeries->images);

        return MediaItemData::from($response);
    }
}
