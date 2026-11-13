<?php

namespace App\Http\Controllers\API\V1;

use App\Models\EventType;
use App\Data\V1\EventTypeData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class GroupedEventTypeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $eventTypes = cache()->remember('eventTypesGroupedByType.v3', now()->addHours(6), function (): array {
            return EventType::query()
                ->select('event_types.*')
                ->whereNotNull('type')
                ->withPublicListingCounts()
                ->orderBy('order')
                ->get()
                ->groupBy('type')
                ->map(fn ($group) => EventTypeData::collect($group))
                ->toArray();
        });

        return response()->json($eventTypes);
    }
}
