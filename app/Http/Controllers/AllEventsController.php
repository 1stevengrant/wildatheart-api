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
        $events = Event::query()
            ->approved()
            ->active()
            ->notPrivate()
            ->with(['eventType', 'host'])
            ->orderBy('start_date', 'asc')
            ->get();

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

        return response()->json(EventResource::collection($offsetEvents));
    }
}
