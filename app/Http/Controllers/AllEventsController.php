<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Data\EventData;
use Illuminate\Http\Request;
use App\Services\EventOffsettingService;

class AllEventsController extends Controller
{
    public function __construct(
        private readonly EventOffsettingService $eventOffsettingService
    ) {}

    public function __invoke(Request $request)
    {
        $startTime = microtime(true);
        $startEventsTime = microtime(true);
        $events = cache()->remember('all_events', now()->addMinutes(15), function () {
            return Event::query()
                ->published()
                ->active()
                ->public()
                ->with(['eventType', 'user', 'managers'])
                ->orderBy('start_date')
                ->get();
        });
        $endEventsTime = microtime(true);

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

        $endOffsetTime = microtime(true);

        $endCollectionTime = microtime(true);

        return response()->json([
            'data' => EventData::collect($offsetEvents),
            'meta' => [
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'events_time_ms' => round(($endEventsTime - $startEventsTime) * 1000, 2),
                'offset_time_ms' => round(($endOffsetTime - $endEventsTime) * 1000, 2),
                'collection_time_ms' => round(($endCollectionTime - $endOffsetTime) * 1000, 2),
            ],
        ]);
    }
}
