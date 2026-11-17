<?php

namespace App\Http\Controllers\Internal;

use Inertia\Response;
use App\Models\EventType;
use App\Http\Controllers\Controller;
use App\Data\InternalEventSearchData;
use Illuminate\Support\Facades\Cache;
use App\Enums\Internal\EventSearchField;
use App\Data\InternalEventSearchFiltersData;
use App\Actions\Event\BuildInternalEventSearchQuery;
use App\Http\Requests\Internal\InternalEventSearchRequest;

class InternalEventSearchController extends Controller
{
    public function __invoke(InternalEventSearchRequest $request): Response
    {
        $criteria = $request->criteria();

        $results = Cache::remember(
            $criteria->pageCacheKey($request->integer('page', 1)),
            now()->addMinutes(5),
            fn (): array => InternalEventSearchData::paginatedResults(
                BuildInternalEventSearchQuery::run($criteria)->paginate(25)->withQueryString()
            ),
        );

        $eventTypes = Cache::remember('event_types_list.v2', now()->addHours(1), function (): array {
            return EventType::query()->orderBy('name')->get(['id', 'name'])->toArray();
        });

        return inertia('Internal/EventSearchPage', [
            'results' => $results,
            'filters' => InternalEventSearchFiltersData::fromCriteria(
                $criteria,
                fields: $request->fields(),
                placeName: $request->placeName(),
            ),
            'availableFields' => EventSearchField::toOptions(),
            'eventTypes' => $eventTypes,
        ]);
    }
}
