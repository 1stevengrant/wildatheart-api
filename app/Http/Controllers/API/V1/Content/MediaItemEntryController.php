<?php

namespace App\Http\Controllers\API\V1\Content;

use App\Http\Controllers\Controller;
use App\Services\BlockBuilderService;
use App\Services\EntryBuilderService;
use App\Services\SubsplashService;
use Carbon\Carbon;
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
                ->where('date', '<=', Carbon::now())
                ->first();
        }

        if (!$mediaItem) {
            return response()->json(['error' => 'Media not found'], 404);
        }

        $entryBuilder = new EntryBuilderService();

        $response = $entryBuilder->buildResponseFromEntry($mediaItem);


        $mediaItemsInSeries = Entry::query()
            ->where('collection', 'media_item')
            ->where('data->series', 'like', "%{$mediaItem->series}")
            ->get();

        // $mediaItem = $mediaItem->toArray();
        $response['mediaItems'] = $mediaItemsInSeries->toArray();

        $blockBuilder = new BlockBuilderService();

        $response['blocks'] = $blockBuilder->build($mediaItem->blocks);

        return response()->json($response);
    }
}
