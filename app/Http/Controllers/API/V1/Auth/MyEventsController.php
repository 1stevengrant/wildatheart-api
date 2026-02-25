<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Models\Event;
use App\Data\EventData;
use App\Data\V1\MyEventsRequestData;
use App\Http\Controllers\Controller;

class MyEventsController extends Controller
{
    public function __invoke(MyEventsRequestData $request)
    {
        $user = auth()->user();

        $query = Event::query()
            ->controllableByUser($user)
            ->notAutoUnpublished()
            ->with(['user', 'eventType', 'managers']);

        if ($request->status === 'active') {
            $query->published()->active();
        } else {
            $query->where(function ($q) {
                $q->unpublished()
                    ->orWhere(function ($sub) {
                        $sub->published()->whereNot(fn ($inner) => $inner->active());
                    });
            })
                ->orderByRaw('start_date < NOW()')
                ->orderBy('start_date');
        }

        $events = $query->paginate($request->per_page)
            ->through(fn ($event) => EventData::fromModel($event));

        return response()->json([
            'data' => $events->items(),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }
}
