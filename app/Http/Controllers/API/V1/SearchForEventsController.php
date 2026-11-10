<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Builders\EventBuilder;
use Statamic\Facades\GlobalSet;
use App\Data\FeaturedContentData;
use App\Data\V1\SearchEventsData;
use Illuminate\Http\JsonResponse;
use App\Services\GeographyService;
use App\Services\EventCacheService;
use App\Data\V1\EventCollectionData;
use App\Http\Controllers\Controller;
use App\Services\EventOffsettingService;
use Illuminate\Database\Eloquent\Collection;
use App\Support\Schedule\PublicScheduleDates;

class SearchForEventsController extends Controller
{
    /**
     * How long, in seconds, a search response may be reused by shared caches.
     */
    private const CACHE_TTL_SECONDS = 60;

    public function __construct(
        private readonly EventOffsettingService $eventOffsettingService,
        private readonly EventCacheService $eventCacheService,
        private readonly GeographyService $geographyService
    ) {}

    public function __invoke(SearchEventsData $data): JsonResponse
    {
        $cacheKey = $this->eventCacheService->generateSearchCacheKey($data->toArray());

        $payload = cache()->remember($cacheKey, now()->addMinutes(15), function () use ($data): array {
            if ($data->isNearestMode()) {
                return $this->nearestPayload($data);
            }

            if ($data->isCenterPointMode()) {
                return $this->centerPointPayload($data);
            }

            return $this->boundingBoxPayload($data);
        });

        return $this->cacheableResponse($payload);
    }

    /**
     * @return array{events: array<int, array<string, mixed>>, radius: ?float, total_count: int, pagination: null, featuredContent: null}
     */
    private function nearestPayload(SearchEventsData $data): array
    {
        $events = $this->filteredQuery($data)
            ->nearestTo($data->latitude, $data->longitude, $data->nearestLimit())
            ->with(['eventType', 'user', ...PublicScheduleDates::RELATIONS])
            ->get();

        return [
            'events' => $this->presentEvents($events),
            'radius' => $this->effectiveRadius($events),
            'total_count' => $events->count(),
            'pagination' => null,
            'featuredContent' => null,
        ];
    }

    /**
     * @return array{events: array<int, array<string, mixed>>, radius: float, total_count: int, pagination: ?array, featuredContent: null}
     */
    private function centerPointPayload(SearchEventsData $data): array
    {
        $radius = $data->radius ?? 100;

        $query = $this->filteredQuery($data);

        if ($data->min_radius !== null) {
            $query->withinRing($data->latitude, $data->longitude, $data->min_radius, $radius);
        } else {
            $query->withinRadius($data->latitude, $data->longitude, $radius);
        }

        $query->with(['eventType', 'user', ...PublicScheduleDates::RELATIONS]);

        if (! $data->isPaginated()) {
            $events = $query->get();

            return [
                'events' => $this->presentEvents($events),
                'radius' => $radius,
                'total_count' => $events->count(),
                'pagination' => null,
                'featuredContent' => null,
            ];
        }

        $paginator = $query->paginate(
            perPage: $data->perPage(),
            page: $data->currentPage()
        );

        return [
            'events' => $this->presentEvents($paginator->getCollection()),
            'radius' => $radius,
            'total_count' => $paginator->total(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_more' => $paginator->hasMorePages(),
            ],
            'featuredContent' => null,
        ];
    }

    /**
     * @return array{events: array<int, array<string, mixed>>, featuredContent: ?array<string, mixed>}
     */
    private function boundingBoxPayload(SearchEventsData $data): array
    {
        $events = $this->filteredQuery($data)
            ->withinBoundingBox(
                $data->sw_latitude,
                $data->sw_longitude,
                $data->ne_latitude,
                $data->ne_longitude
            )
            ->orderBy('start_date')
            ->with(['eventType', 'user', ...PublicScheduleDates::RELATIONS])
            ->get();

        return [
            'events' => $this->presentEvents($events),
            'featuredContent' => $this->featuredContentFor($data),
        ];
    }

    private function filteredQuery(SearchEventsData $data): EventBuilder
    {
        return Event::query()
            ->published()
            ->active()
            ->public()
            ->filterBySex($data->sex)
            ->filterByTypeGroup($data->type_group)
            ->filterByEventTypes($data->types)
            ->betweenDates($data->start_date, $data->end_date);
    }

    /**
     * @param  Collection<int, Event>  $events
     * @return array<int, array<string, mixed>>
     */
    private function presentEvents($events): array
    {
        return $this->eventOffsettingService
            ->offsetEvents($events)
            ->map(fn (Event $event): array => EventCollectionData::fromEvent($event)->toArray())
            ->values()
            ->toArray();
    }

    /**
     * The radius, in miles, that a client needs to cover every event in the result.
     *
     * @param  Collection<int, Event>  $events
     */
    private function effectiveRadius($events): ?float
    {
        if ($events->isEmpty()) {
            return null;
        }

        return (float) ceil((float) $events->max('distance'));
    }

    private function featuredContentFor(SearchEventsData $data): ?array
    {
        $globalSet = GlobalSet::findByHandle('local_platform');

        if (! $globalSet) {
            return null;
        }

        $siteData = $globalSet->inDefaultSite()->data();

        if (! $siteData->get('featured_content_enabled')) {
            return null;
        }

        $showInRegions = $siteData->get('show_in_regions', []);

        $visibleRegions = $this->geographyService->getVisibleRegions(
            $data->sw_latitude,
            $data->sw_longitude,
            $data->ne_latitude,
            $data->ne_longitude
        );

        if (! empty($showInRegions) && ! array_intersect($showInRegions, $visibleRegions)) {
            return null;
        }

        return FeaturedContentData::fromStatamicData($siteData)->toArray();
    }

    private function cacheableResponse(array $payload): JsonResponse
    {
        return response()
            ->json($payload)
            ->setPublic()
            ->setMaxAge(self::CACHE_TTL_SECONDS)
            ->setSharedMaxAge(self::CACHE_TTL_SECONDS);
    }
}
