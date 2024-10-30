<?php

namespace App\Http\Controllers;

use App\Http\Resources\API\EventResource;
use App\Models\Event;
use App\Services\EventOffsettingService;
use Illuminate\Http\Request;

class AllEventsController extends Controller
{
    public function __construct(
        private readonly EventOffsettingService $eventOffsettingService
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $startTime = microtime(true);
        $startEventsTime = microtime(true);
        $events = cache()->remember('all_events', now()->addMinutes(15), function () {
            return Event::query()
                ->approved()
                ->active()
                ->notPrivate()
                ->with(['eventType', 'host'])
                ->orderBy('start_date', 'asc')
                ->get();
        });
        $endEventsTime = microtime(true);

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

        $endOffsetTime = microtime(true);

        $endCollectionTime = microtime(true);

        return response()->json([
            'data' => EventResource::collection($offsetEvents),
            'meta' => [
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'events_time_ms' => round(($endEventsTime - $startEventsTime) * 1000, 2),
                'offset_time_ms' => round(($endOffsetTime - $endEventsTime) * 1000, 2),
                'collection_time_ms' => round(($endCollectionTime - $endOffsetTime) * 1000, 2),
            ],
        ]);
    }
}
