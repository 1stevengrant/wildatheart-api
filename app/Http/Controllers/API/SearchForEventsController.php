<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Data\EventData;
use App\Data\FeaturedContentData;
use App\Data\FeaturedContentItemData;
use App\Services\GeographyService;
use App\Services\EventCacheService;
use App\Http\Controllers\Controller;
use App\Services\EventOffsettingService;
use App\Http\Requests\API\SearchForEventsRequest;

class SearchForEventsController extends Controller
{
    public function __construct(
        private readonly EventOffsettingService $eventOffsettingService,
        private readonly EventCacheService $eventCacheService,
        private readonly GeographyService $geographyService
    ) {}

    public function __invoke(SearchForEventsRequest $request)
    {
        $validatedData = $request->validated();

        $cacheKey = $this->eventCacheService->generateSearchCacheKey($validatedData);

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
                ->orderBy('start_date')
                ->get();
        });

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

        // Get featured content
        $featuredContent = null;
        $globalSet = \Statamic\Facades\GlobalSet::findByHandle('local_platform');
        if ($globalSet) {
            $data = $globalSet->inDefaultSite()->data();
            $featuredContentData = FeaturedContentData::fromStatamicData($data);

            if ($featuredContentData && $featuredContentData->items->count() > 0) {
                // Get visible regions for the current viewport
                $visibleRegions = $this->geographyService->getVisibleRegions(
                    $validatedData['sw_latitude'],
                    $validatedData['sw_longitude'],
                    $validatedData['ne_latitude'],
                    $validatedData['ne_longitude']
                );

                // Filter items by regions that are visible in the current viewport
                $filteredItems = $featuredContentData->items->filter(function ($item) use ($visibleRegions) {
                    // If no regions specified for the item, show it everywhere
                    if (empty($item->show_in_regions)) {
                        return true;
                    }
                    // Check if any of the item's regions are visible
                    return !empty(array_intersect($item->show_in_regions, $visibleRegions));
                });

                // Only return featured content if there are items to show
                if ($filteredItems->count() > 0) {
                    $featuredContent = new FeaturedContentData(
                        items: $filteredItems
                    );
                }
            }
        }

        return response()->json([
            'events' => EventData::collection($offsetEvents),
            'featuredContent' => $featuredContent,
        ]);

    }
}
