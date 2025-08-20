<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Data\EventData;
use App\Http\Controllers\Controller;
use App\Services\EventOffsettingService;
use App\Services\GeographicRegionService;
use App\Http\Requests\API\SearchForEventsRequest;

class SearchForEventsController extends Controller
{
    public function __construct(
        private readonly EventOffsettingService $eventOffsettingService,
        private readonly GeographicRegionService $geographicRegionService
    ) {}

    public function __invoke(SearchForEventsRequest $request)
    {
        $validatedData = $request->validated();

        $cacheKey = $this->generateCacheKey($validatedData);

        $events = cache()->remember($cacheKey, now()->addMinutes(15), function () use ($validatedData) {
            // Get regular events within the bounding box
            $regularEvents = Event::query()
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
                ->get();

            // Get always show events that match the filters but are outside the viewport
            $alwaysShowEvents = Event::query()
                ->approved()
                ->active()
                ->notPrivate()
                ->alwaysShow()
                ->filterBySex($validatedData['sex'])
                ->filterByTypeGroup($validatedData['type_group'])
                ->filterByEventTypes($validatedData['types'])
                ->betweenDates($validatedData['start_date'], $validatedData['end_date'])
                ->whereNotIn('id', $regularEvents->pluck('id'))
                ->with(['eventType', 'user'])
                ->get();

            // Merge always show events with regular events
            $regularEvents = $regularEvents->merge($alwaysShowEvents);

            // Determine the region from the viewport
            $viewportRegion = $this->geographicRegionService->getRegionFromViewport(
                $validatedData['sw_latitude'],
                $validatedData['sw_longitude'],
                $validatedData['ne_latitude'],
                $validatedData['ne_longitude']
            );

            // If we can determine the region, get featured events from that region
            if ($viewportRegion) {
                $regionCountries = $this->geographicRegionService->getCountriesInRegion($viewportRegion);

                if (! empty($regionCountries)) {
                    // Get featured events from the same region that aren't already in results
                    $featuredEvents = Event::query()
                        ->approved()
                        ->active()
                        ->notPrivate()
                        ->featured()
                        ->filterBySex($validatedData['sex'])
                        ->filterByTypeGroup($validatedData['type_group'])
                        ->filterByEventTypes($validatedData['types'])
                        ->betweenDates($validatedData['start_date'], $validatedData['end_date'])
                        ->featuredInRegion($regionCountries)
                        ->whereNotIn('id', $regularEvents->pluck('id'))
                        ->with(['eventType', 'user'])
                        ->get();

                    // Merge featured events with existing events
                    $regularEvents = $regularEvents->merge($featuredEvents);
                }
            }

            // Sort events: always show first, then featured, then by start date
            return $regularEvents->sort(function ($a, $b) {
                // Always show events come first
                if ($a->isAlwaysShow() && ! $b->isAlwaysShow()) {
                    return -1;
                }
                if (! $a->isAlwaysShow() && $b->isAlwaysShow()) {
                    return 1;
                }

                // Featured events come next
                if ($a->isFeatured() && ! $b->isFeatured()) {
                    return -1;
                }
                if (! $a->isFeatured() && $b->isFeatured()) {
                    return 1;
                }

                // Then sort by start date
                $aDate = $a->start_date ? strtotime($a->start_date) : 0;
                $bDate = $b->start_date ? strtotime($b->start_date) : 0;

                return $aDate - $bDate;
            })->values();
        });

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

        return response()->json(EventData::collection($offsetEvents));

    }

    private function generateCacheKey(array $validatedData): string
    {
        return 'events_search_' . md5(json_encode($validatedData));
    }
}
