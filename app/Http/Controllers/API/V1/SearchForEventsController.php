<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use Statamic\Facades\GlobalSet;
use App\Data\FeaturedContentData;
use App\Data\V1\SearchEventsData;
use App\Services\GeographyService;
use App\Services\EventCacheService;
use App\Data\V1\EventCollectionData;
use App\Http\Controllers\Controller;
use App\Services\EventOffsettingService;

class SearchForEventsController extends Controller
{
    public function __construct(
        private readonly EventOffsettingService $eventOffsettingService,
        private readonly EventCacheService $eventCacheService,
        private readonly GeographyService $geographyService
    ) {}

    public function __invoke(SearchEventsData $data)
    {
        $cacheKey = $this->eventCacheService->generateSearchCacheKey($data->toArray());

        if ($data->isCenterPointMode()) {
            return cache()->remember($cacheKey, now()->addMinutes(15), function () use ($data) {
                $radius = $data->radius ?? 100;
                $query = Event::query()
                    ->published()->active()->public()
                    ->filterBySex($data->sex)
                    ->filterByTypeGroup($data->type_group)
                    ->filterByEventTypes($data->types)
                    ->betweenDates($data->start_date, $data->end_date);

                if ($data->min_radius !== null) {
                    $query->withinRing($data->latitude, $data->longitude, $data->min_radius, $radius);
                } elseif ($data->nearest !== null) {
                    $query->nearestTo($data->latitude, $data->longitude, $data->nearest);
                } else {
                    $query->withinRadius($data->latitude, $data->longitude, $radius);
                }

                $events = $query->with(['eventType', 'user'])->get();
                $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

                return response()->json([
                    'events' => $offsetEvents->map(fn (Event $event) => EventCollectionData::fromEvent($event)),
                    'radius' => $radius,
                    'total_count' => $events->count(),
                    'featuredContent' => null,
                ]);
            });
        }

        $events = cache()->remember($cacheKey, now()->addMinutes(15), function () use ($data) {
            return Event::query()
                ->published()
                ->active()
                ->public()
                ->filterBySex($data->sex)
                ->filterByTypeGroup($data->type_group)
                ->filterByEventTypes($data->types)
                ->betweenDates($data->start_date, $data->end_date)
                ->withinBoundingBox(
                    $data->sw_latitude,
                    $data->sw_longitude,
                    $data->ne_latitude,
                    $data->ne_longitude
                )
                ->orderBy('start_date')
                ->with(['eventType', 'user'])
                ->get();
        });

        $offsetEvents = $this->eventOffsettingService->offsetEvents($events);

        $featuredContent = null;
        $globalSet = GlobalSet::findByHandle('local_platform');

        if ($globalSet) {
            $siteData = $globalSet->inDefaultSite()->data();

            if ($siteData->get('featured_content_enabled')) {
                $showInRegions = $siteData->get('show_in_regions', []);

                $visibleRegions = $this->geographyService->getVisibleRegions(
                    $data->sw_latitude,
                    $data->sw_longitude,
                    $data->ne_latitude,
                    $data->ne_longitude
                );

                if (empty($showInRegions) || array_intersect($showInRegions, $visibleRegions)) {
                    $featuredContent = FeaturedContentData::fromStatamicData($siteData);
                }
            }
        }

        return response()->json([
            'events' => $offsetEvents->map(fn (Event $event) => EventCollectionData::fromEvent($event)),
            'featuredContent' => $featuredContent,
        ]);
    }
}
