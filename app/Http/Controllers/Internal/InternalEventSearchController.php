<?php

namespace App\Http\Controllers\Internal;

use App\Models\Event;
use App\Models\EventType;
use App\Http\Controllers\Controller;
use App\Data\InternalEventSearchData;
use App\Enums\Internal\EventSearchField;
use App\Http\Requests\Internal\InternalEventSearchRequest;

class InternalEventSearchController extends Controller
{
    public function __invoke(InternalEventSearchRequest $request)
    {
        $results = [];

        if ($request->hasSearchCoordinates()) {
            $results = $this->searchEvents(
                $request->lat(),
                $request->lng(),
                $request->radius(),
                $request->eventTypeIds()
            );
        }

        return inertia('Internal/EventSearchPage', [
            'results' => $results,
            'filters' => [
                'lat' => $request->lat(),
                'lng' => $request->lng(),
                'radius' => $request->radius(),
                'event_type_ids' => $request->eventTypeIds(),
                'fields' => $request->fields(),
                'place_name' => $request->placeName(),
            ],
            'availableFields' => EventSearchField::toOptions(),
            'eventTypes' => EventType::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function searchEvents(float $lat, float $lng, float $radius, array $eventTypeIds): array
    {
        $query = Event::query()
            ->withinRadius($lat, $lng, $radius)
            ->with(['user', 'eventType'])
            ->published();

        if (! empty($eventTypeIds)) {
            $query->whereIn('event_type_id', $eventTypeIds);
        }

        return $query->get()
            ->map(fn (Event $event) => InternalEventSearchData::fromModel($event, $event->distance))
            ->toArray();
    }
}
