<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Data\EventData;
use App\Data\FeaturedContentData;
use App\Http\Controllers\Controller;
use App\Services\EventOffsettingService;
use App\Http\Requests\API\SearchForEventsRequest;

class SearchForEventsController extends Controller
{
    public function __construct(
        private readonly EventOffsettingService $eventOffsettingService
    ) {}

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
                $visibleRegions = $this->getVisibleRegions(
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
            'events' => EventData::collection($offsetEvents),
            'featuredContent' => $featuredContent,
        ]);

    }

    private function generateCacheKey(array $validatedData): string
    {
        return 'events_search_' . md5(json_encode($validatedData));
    }

    private function getVisibleRegions($swLat, $swLng, $neLat, $neLng): array
    {
        $regions = [
            'europe' => [
                'north' => 71.0,
                'south' => 36.0,
                'west' => -25.0,
                'east' => 40.0,
            ],
            'north_america' => [
                'north' => 83.0,
                'south' => 15.0,
                'west' => -168.0,
                'east' => -52.0,
            ],
            'south_america' => [
                'north' => 13.0,
                'south' => -56.0,
                'west' => -82.0,
                'east' => -34.0,
            ],
            'africa' => [
                'north' => 37.0,
                'south' => -35.0,
                'west' => -18.0,
                'east' => 52.0,
            ],
            'asia' => [
                'north' => 77.0,
                'south' => -10.0,
                'west' => 26.0,
                'east' => 180.0,
            ],
            'oceania' => [
                'north' => -10.0,
                'south' => -47.0,
                'west' => 112.0,
                'east' => 180.0,
            ],
        ];

        $visibleRegions = [];

        foreach ($regions as $region => $coords) {
            // Check if there's any overlap between viewport and region (latitude)
            $overlapsLat = !($neLat < $coords['south'] || $swLat > $coords['north']);
            
            // Handle longitude wrap-around for regions that cross the date line
            $overlapsLng = false;
            
            // Check if the viewport crosses the date line
            $viewportCrossesDateLine = $swLng > $neLng;
            
            if ($viewportCrossesDateLine) {
                // Viewport crosses the date line
                $overlapsLng = ($coords['east'] >= $swLng && $coords['east'] <= 180) || 
                             ($coords['west'] >= -180 && $coords['west'] <= $neLng) ||
                             ($coords['west'] <= $neLng && $coords['east'] >= $swLng);
            } elseif ($coords['west'] > $coords['east']) {
                // Region crosses the date line, viewport doesn't
                $overlapsLng = $neLng >= $coords['west'] || $swLng <= $coords['east'];
            } else {
                // Normal case: neither crosses the date line
                $overlapsLng = !($neLng < $coords['west'] || $swLng > $coords['east']);
            }

            if ($overlapsLat && $overlapsLng) {
                $visibleRegions[] = $region;
            }
        }

        return $visibleRegions;
    }
}
