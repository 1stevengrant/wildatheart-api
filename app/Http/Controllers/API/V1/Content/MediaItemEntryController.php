<?php

namespace App\Http\Controllers\API\V1\Content;

use App\DataTypeObjects\MediaItemData;
use App\Http\Controllers\API\MediaItemDataController;
use App\Http\Controllers\Controller;
use App\Services\BlockBuilderService;
use App\Services\EntryBuilderService;
use App\Services\ImageBuilderService;
use Facades\Statamic\CP\LivePreview;
use Statamic\Entries\Entry;
use Statamic\Facades\Token;

class MediaItemEntryController extends Controller
{
    public function media($slug)
    {

        // Check for token and live-preview query parameters
        $request = request();
        $token = $request->query('token', false);
        $livePreview = $request->query('live-preview', false);

        // If token is present or live-preview is true, adjust the query to include unpublished and future items

        $mediaItem = null;

        if ($token && $livePreview) {
            // Skip published and date checks for live preview or token access

            $token = Token::find($token);
            $mediaItem = LivePreview::item($token);
        } else {

            $mediaItem = Entry::query()
                ->where('collection', 'media_item')
                ->where('slug', $slug)
                ->where('published', true)
                ->where('date', '<=', now())
                ->first();
        }

        if (! $mediaItem) {
            return response()->json(['error' => 'Media not found'], 404);
        }

        // Adding this as workaround until I can refactor into a service
        if ($mediaItem->blueprint->handle === 'subsplash_media_item') {
            return app(MediaItemDataController::class)->buildSubsplashMediaItemResponse($mediaItem);
        }

        $entryBuilder = new EntryBuilderService;

        $response = $entryBuilder->buildResponseFromEntry($mediaItem);

        $mediaItemsInSeries = Entry::query()
            ->where('collection', 'media_item')
            ->where('data->series', 'like', "%{$mediaItem->series}")
            ->get();

        // $mediaItem = $mediaItem->toArray();
        $response['mediaItems'] = $mediaItemsInSeries->toArray();

        if (isset($mediaItem->blocks)) {
            $blockBuilder = new BlockBuilderService;
            $response['blocks'] = $blockBuilder->build($mediaItem->blocks);
        }

        $imageBuilder = new ImageBuilderService;

        $response['images'] = $imageBuilder->build($mediaItem->images);

        return MediaItemData::from($response);
    }
}
