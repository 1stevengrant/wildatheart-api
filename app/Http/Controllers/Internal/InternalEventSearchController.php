<?php

namespace App\Http\Controllers\Internal;

use App\Models\Event;
use App\Models\EventType;
use App\Http\Controllers\Controller;
use App\Data\InternalEventSearchData;
use Illuminate\Support\Facades\Cache;
use App\Enums\Internal\EventSearchField;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Requests\Internal\InternalEventSearchRequest;

class InternalEventSearchController extends Controller
{
    private const CACHE_TTL_MINUTES = 5;

    public function __invoke(InternalEventSearchRequest $request)
    {
        $cacheKey = $this->buildCacheKey($request);

        $results = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($request) {
            $paginator = $this->searchEvents(
                $request->hasSearchCoordinates() ? $request->lat() : null,
                $request->hasSearchCoordinates() ? $request->lng() : null,
                $request->hasSearchCoordinates() ? $request->radius() : null,
                $request->eventTypeIds(),
                $request->activeOnly(),
                $request->includeDeleted(),
                $request->createdFrom(),
                $request->createdTo()
            );

            return $this->transformResults($paginator);
        });

        $eventTypes = Cache::remember('event_types_list', now()->addHours(1), function () {
            return EventType::query()->orderBy('name')->get(['id', 'name']);
        });

        return inertia('Internal/EventSearchPage', [
            'results' => $results,
            'filters' => [
                'lat' => $request->hasSearchCoordinates() ? $request->lat() : null,
                'lng' => $request->hasSearchCoordinates() ? $request->lng() : null,
                'radius' => $request->hasSearchCoordinates() ? $request->radius() : null,
                'event_type_ids' => $request->eventTypeIds(),
                'fields' => $request->fields(),
                'place_name' => $request->placeName(),
                'active_only' => $request->activeOnly(),
                'include_deleted' => $request->includeDeleted(),
                'created_from' => $request->createdFrom(),
                'created_to' => $request->createdTo(),
            ],
            'availableFields' => EventSearchField::toOptions(),
            'eventTypes' => $eventTypes,
        ]);
    }

    private function buildCacheKey(InternalEventSearchRequest $request): string
    {
        $params = [
            'lat' => $request->hasSearchCoordinates() ? $request->lat() : null,
            'lng' => $request->hasSearchCoordinates() ? $request->lng() : null,
            'radius' => $request->hasSearchCoordinates() ? $request->radius() : null,
            'event_type_ids' => $request->eventTypeIds(),
            'active_only' => $request->activeOnly(),
            'include_deleted' => $request->includeDeleted(),
            'created_from' => $request->createdFrom(),
            'created_to' => $request->createdTo(),
            'page' => $request->input('page', 1),
        ];

        return 'event_search:' . md5(serialize($params));
    }

    private function searchEvents(?float $lat, ?float $lng, ?float $radius, array $eventTypeIds, bool $activeOnly, bool $includeDeleted, ?string $createdFrom = null, ?string $createdTo = null): LengthAwarePaginator
    {
        $query = Event::query()->with(['user', 'eventType']);

        if ($lat !== null && $lng !== null && $radius !== null) {
            $query->withinRadius($lat, $lng, $radius);
        }

        if ($activeOnly) {
            $query->published();
        }

        if ($includeDeleted) {
            $query->withTrashed();
        }

        if (! empty($eventTypeIds)) {
            $query->whereIn('event_type_id', $eventTypeIds);
        }

        if ($createdFrom) {
            $query->where('created_at', '>=', $createdFrom);
        }

        if ($createdTo) {
            $query->where('created_at', '<=', "{$createdTo} 23:59:59");
        }

        return $query->paginate(25)->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    private function transformResults(LengthAwarePaginator $paginator): array
    {
        $data = collect($paginator->items())
            ->map(fn (Event $event) => InternalEventSearchData::fromModel($event, $event->distance ?? null))
            ->toArray();

        return [
            'data' => $data,
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
                'links' => $paginator->linkCollection()->toArray(),
            ],
        ];
    }
}
