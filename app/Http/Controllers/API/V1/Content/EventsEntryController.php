<?php

namespace App\Http\Controllers\API\V1\Content;

use Statamic\Entries\Entry;
use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\EntryBuilderService;
use App\DataTypeObjects\API\V1\Content\Events\EventsShowResponse;
use App\Http\Controllers\API\V1\Content\Concerns\ResolvesLivePreview;

class EventsEntryController extends Controller
{
    use ResolvesLivePreview;

    public function __construct(private readonly EntryBuilderService $entryBuilder) {}

    public function index()
    {
        $events = EntryQueryBuilder::events()
            ->published()
            ->recentFirst()
            ->get();

        $eventsResponse = $events->map($this->buildEventResponse(...));

        return EventsShowResponse::collect($eventsResponse);
    }

    public function show($slug)
    {
        $event = $this->resolveLivePreview();

        if (! $event) {
            $event = EntryQueryBuilder::events()
                ->published()
                ->bySlug($slug)
                ->where(function ($query) {
                    $query->where('date', '<=', now())
                        ->orWhere('data->show_in_collections', true);
                })
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
        $response = $this->entryBuilder->buildResponseFromEntry($event->blueprint->handle === 'video_study' ? $event->related_content : $event);

        if (isset($response['background_video_url']) && is_array($response['background_video_url'])) {
            $response['background_video_url'] = $response['background_video_url']['url'];
        }

        if (isset($response['featured_video_url']) && is_array($response['featured_video_url'])) {
            $response['featured_video_url'] = $response['featured_video_url']['url'];
        }

        return $response;
    }
}
