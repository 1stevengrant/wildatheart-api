<?php

namespace App\Http\Controllers\User;

use App\Data\EventData;
use App\Models\EventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MyInactiveEventsController extends Controller
{
    public function __invoke(Request $request)
    {
        $events = \App\Models\Event::query()
            ->controllableByUser(auth()->user())
            ->where(function ($query) {
                $query->unpublished()
                    ->orWhere(function ($q) {
                        $q->published()->whereNot(fn ($sub) => $sub->active());
                    });
            })
            ->orderByRaw('start_date < NOW()')
            ->orderBy('start_date')
            ->with(['user', 'eventType', 'managers'])
            ->paginate()
            ->through(fn ($event) => EventData::fromModel($event));

        return inertia('Profile/MyInactiveEventsPage', [
            'events' => $events,
            'eventTypes' => EventType::query()->active()->get(),
        ]);
    }
}
