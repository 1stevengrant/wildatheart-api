<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use App\Models\EventType;
use App\Data\V1\EventTypeData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class GroupedEventTypeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $eventTypes = cache()->remember('eventTypesGroupedByType', now()->addHours(6), function () {
            return EventType::query()
                ->select('event_types.*')
                ->whereNotNull('type')
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
                ->orderBy('order')
                ->get()
                ->groupBy('type')
                ->map(fn ($group) => EventTypeData::collect($group))
                ->toArray();
        });

        return response()->json($eventTypes);
    }
}
