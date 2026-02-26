<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Models\EventType;
use App\Models\EventTypeGroup;
use App\Data\V1\EventTypeData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class GroupedEventTypeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $eventTypes = cache()->remember('eventTypesGroupedByType', now()->addHours(6), function () {
            return EventTypeGroup::query()
                ->with(['eventTypes' => function ($query) {
                    $query->with('type')
                        ->select('event_types.*')
                        ->withCount([
                            'events as listings_count' => function ($query) {
                                $query->whereNotNull('approved_at')
                                    ->where('is_private', false)
                                    ->where(function ($q) {
                                        $q->where('start_date', '>=', now())
                                            ->orWhereNull('start_date');
                                    });
                            },
                        ])
                        ->addSelect([
                            'countries_count' => Event::select(DB::raw('COUNT(DISTINCT country)'))
                                ->whereColumn('event_type_id', 'event_types.id')
                                ->whereNotNull('country')
                                ->whereNotNull('approved_at')
                                ->where('is_private', false)
                                ->where(function ($q) {
                                    $q->where('start_date', '>=', now()->toDateString())
                                        ->orWhereNull('start_date');
                                }),
                        ])
                        ->orderBy('order');
                }])
                ->orderByRaw('sort_order IS NULL, sort_order ASC')
                ->get()
                ->map(fn (EventTypeGroup $group) => [
                    'name' => $group->name,
                    'icon' => $group->icon,
                    'width' => $group->width,
                    'height' => $group->height,
                    'summary' => $group->summary,
                    'event_types' => EventTypeData::collection($group->eventTypes)->toArray(),
                ])
                ->values()
                ->toArray();
        });

        return response()->json($eventTypes);
    }
}
