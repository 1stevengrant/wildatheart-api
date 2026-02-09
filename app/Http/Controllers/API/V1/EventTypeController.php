<?php

namespace App\Http\Controllers\API\V1;

use App\Data\V1\EventTypeData;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventTypeController extends Controller
{
    public function __invoke(Request $request)
    {
        $eventTypes = cache()->remember('eventTypesWithCounts', now()->addHours(6), function () {
            return EventType::query()
                ->select('event_types.*')
                ->active()
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
                ->get();
        });

        return response()->json(EventTypeData::collection($eventTypes));
    }
}
