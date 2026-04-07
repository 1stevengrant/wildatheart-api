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
        $events = cache()->remember('all_events', now()->addMinutes(15), function () {
            return Event::query()
                ->published()
                ->active()
                ->public()
                ->with(['eventType', 'user', 'managers'])
                ->orderBy('start_date')
                ->get();
        });
        $eventsTime = microtime(true);

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);
        $offsetTime = microtime(true);

        $data = EventData::collect($offsetEvents);
        $collectionTime = microtime(true);

        return response()->json([
            'data' => $data,
            'meta' => [
                'response_time_ms' => round(($collectionTime - $startTime) * 1000, 2),
                'events_time_ms' => round(($eventsTime - $startTime) * 1000, 2),
                'offset_time_ms' => round(($offsetTime - $eventsTime) * 1000, 2),
                'collection_time_ms' => round(($collectionTime - $offsetTime) * 1000, 2),
            ],
        ]);
    }
}
