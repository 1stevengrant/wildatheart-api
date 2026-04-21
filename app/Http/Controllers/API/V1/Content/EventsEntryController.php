<?php

namespace App\Http\Controllers\API\V1\Content;

use App\Builders\EntryQueryBuilder;
use App\Http\Controllers\Controller;
use App\Services\EventResponseBuilder;
use App\DataTypeObjects\API\V1\Content\Events\EventsShowResponse;
use App\Http\Controllers\API\V1\Content\Concerns\ResolvesLivePreview;

class EventsEntryController extends Controller
{
    use ResolvesLivePreview;

    public function __construct(private readonly EventResponseBuilder $responseBuilder) {}

    public function index()
    {
        $events = EntryQueryBuilder::events()
            ->published()
            ->recentFirst()
            ->get();

        $eventsResponse = $events->map(fn ($event) => $this->responseBuilder->build($event));

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

        $response = $this->responseBuilder->build($event);

        return EventsShowResponse::from($response);
    }
}
