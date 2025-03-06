<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Http\Controllers\Controller;
use App\Services\EventOffsettingService;
use App\Http\Resources\API\EventResource;
use App\Http\Requests\API\SearchForEventsRequest;

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
        $validatedData = $request->validated();

        $cacheKey = $this->generateCacheKey($validatedData);

        $events = cache()->remember($cacheKey, now()->addMinutes(15), function () use ($validatedData) {
            return Event::query()
                ->approved()
                ->active()
                ->notPrivate()
                ->filterBySex($validatedData['sex'])
                ->filterByTypeGroup($validatedData['type_group'])
                ->filterByEventTypes($validatedData['types'])
                ->betweenDates($validatedData['start_date'], $validatedData['end_date'])
                ->withinBoundingBox(
                    $validatedData['sw_latitude'],
                    $validatedData['sw_longitude'],
                    $validatedData['ne_latitude'],
                    $validatedData['ne_longitude']
                )
                ->with(['eventType', 'user'])
                ->orderBy('start_date', 'asc')
                ->get();
        });

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

        return response()->json(EventResource::collection($offsetEvents));

    }

    private function generateCacheKey(array $validatedData): string
    {
        return 'events_search_'.md5(json_encode($validatedData));
    }
}
