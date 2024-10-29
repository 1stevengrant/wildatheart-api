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
        $events = cache()->remember('all_events', now()->addMinutes(15), function () {
            return Event::query()
                ->approved()
                ->active()
                ->notPrivate()
                ->with(['eventType', 'host'])
                ->orderBy('start_date', 'asc')
                ->get();
        });

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

        return response()->json([
            'data' => EventResource::collection($offsetEvents),
            'meta' => [
                'response_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]
        ]);
    }
}
