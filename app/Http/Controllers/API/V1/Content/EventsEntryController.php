<?php

namespace App\Http\Controllers\API\V1\Content;

use Statamic\Entries\Entry;
use Statamic\Facades\Token;
use App\DataTypeObjects\File;
use App\Services\BunnyCDNService;
use App\Http\Controllers\Controller;
use Facades\Statamic\CP\LivePreview;
use App\DataTypeObjects\PlayableFile;
use App\Services\EntryBuilderService;
use App\DataTypeObjects\API\V1\Content\Events\EventsShowResponse;

class EventsEntryController extends Controller
{
    public function index()
    {
        $events = Entry::query()
            ->where('collection', 'events')
            ->where('published', true)
            ->orderBy('date', 'desc')
            ->get();

        $eventsResponse = $events->map(function ($event) {
            return $this->buildEventResponse($event);
        });


        return EventsShowResponse::collection($eventsResponse);
    }

    public function show($slug)
    {

        // Check for token and live-preview query parameters
        $request = request();
        $token = $request->query('token', false);
        $livePreview = $request->query('live-preview', false);

        // If token is present or live-preview is true, adjust the query to include unpublished and future items

        $event = null;

        if ($token && $livePreview) {
            // Skip published and date checks for live preview or token access

            $token = Token::find($token);
            $event = LivePreview::item($token);
        } else {

            $event = Entry::query()
                ->where('collection', 'events')
                ->where('slug', $slug)
                ->where('published', true)
                ->with('event_type')
                ->first();
        }

        if (! $event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $response = $this->buildEventResponse($event);

        return EventsShowResponse::from($response);
    }

    public function buildEventResponse(Entry $event)
    {
        $entryBuilder = new EntryBuilderService;

        $response = $entryBuilder->buildResponseFromEntry($event);
        if (isset($response['background_video_url'])) {
            $response['background_video_url'] = $response['background_video_url']['url'];
        }

        if (isset($response['featured_video_url']) && $response['featured_video_url']) {
            $response['featured_video_url'] = $response['featured_video_url']['url'];
        }


        return $response;
    }
}
