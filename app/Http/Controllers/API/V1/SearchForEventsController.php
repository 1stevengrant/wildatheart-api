<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Data\FeaturedContentData;
use App\Services\GeographyService;
use App\Services\EventCacheService;
use App\Data\V1\EventCollectionData;
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

        // Get featured content if enabled
        $featuredContent = null;
        $globalSet = \Statamic\Facades\GlobalSet::findByHandle('local_platform');
        if ($globalSet) {
            $data = $globalSet->inDefaultSite()->data();

            // Check if featured content is enabled
            if ($data->get('featured_content_enabled')) {
                $showInRegions = $data->get('show_in_regions', []);

                // Check if current viewport contains any of the regions
                $visibleRegions = $this->geographyService->getVisibleRegions(
                    $validatedData['sw_latitude'],
                    $validatedData['sw_longitude'],
                    $validatedData['ne_latitude'],
                    $validatedData['ne_longitude']
                );

                // Show featured content if no regions specified or if regions match
                if (empty($showInRegions) || array_intersect($showInRegions, $visibleRegions)) {
                    $featuredContent = FeaturedContentData::fromStatamicData($data);
                }
            }
        }

        return response()->json([
            'events' => $offsetEvents->map(fn (Event $event) => EventCollectionData::fromEvent($event)),
            'featuredContent' => $featuredContent,
        ]);

    }
}
