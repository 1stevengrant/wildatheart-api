<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SearchForEventsRequest;
use App\Http\Resources\API\EventResource;
use App\Models\Event;
use App\Services\EventOffsettingService;

class SearchForEventsController extends Controller
{
    public function __construct(
        private readonly EventOffsettingService $eventOffsettingService
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(SearchForEventsRequest $request)
    {
        $request->validated();
        $events = Event::query()
            ->approved()
            ->active()
            ->notPrivate()
            ->filterBySex($request->input('sex'))
            ->filterByTypeGroup($request->input('type_group'))
            ->filterByEventTypes($request->input('types'))
            ->betweenDates($request->input('start_date'), $request->input('end_date'))
            ->withinBoundingBox(
                $request->input('sw_latitude'),
                $request->input('sw_longitude'),
                $request->input('ne_latitude'),
                $request->input('ne_longitude')
            )
            ->with(['eventType', 'host'])
            ->orderBy('start_date', 'asc')
            ->get();

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

        return response()->json(EventResource::collection($offsetEvents));
    }
}
