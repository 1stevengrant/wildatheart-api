<?php

namespace App\Http\Controllers\API;

use App\Data\EventMapData;
use Illuminate\Http\JsonResponse;
use App\Services\EventCacheService;
use App\Http\Controllers\Controller;
use App\Services\EventOffsettingService;
use App\Services\PublicEventSearchService;
use App\Http\Requests\API\SearchForEventsRequest;

class SearchForEventsController extends Controller
{
    public function __construct(
        private readonly EventOffsettingService $eventOffsettingService,
        private readonly EventCacheService $eventCacheService,
        private readonly PublicEventSearchService $publicEventSearchService
    ) {}

    public function __invoke(SearchForEventsRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        $cacheKey = $this->eventCacheService->legacySearchCacheKey($validatedData);

        $events = cache()->remember($cacheKey, now()->addMinutes(15), function () use ($validatedData): array {
            $eventModels = $this->publicEventSearchService
                ->query(
                    sex: $validatedData['sex'] ?? null,
                    typeGroup: $validatedData['type_group'] ?? null,
                    types: $validatedData['types'] ?? null,
                    startDate: $validatedData['start_date'] ?? null,
                    endDate: $validatedData['end_date'] ?? null,
                )
                ->withinBoundingBox(
                    $validatedData['sw_latitude'],
                    $validatedData['sw_longitude'],
                    $validatedData['ne_latitude'],
                    $validatedData['ne_longitude']
                )
                ->with(['eventType', 'user', 'managers'])
                ->orderBy('start_date')
                ->get();

            return EventMapData::collect($this->eventOffsettingService->offsetEvents($eventModels))->toArray();
        });

        $featuredContent = $this->publicEventSearchService->featuredContent(
            (float) $validatedData['sw_latitude'],
            (float) $validatedData['sw_longitude'],
            (float) $validatedData['ne_latitude'],
            (float) $validatedData['ne_longitude']
        );

        return response()->json([
            'events' => $events,
            'featuredContent' => $featuredContent,
        ]);
    }
}
