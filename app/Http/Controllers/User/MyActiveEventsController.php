<?php

namespace App\Http\Controllers\User;

use App\Data\EventData;
use App\Models\EventType;
use App\Http\Controllers\Controller;

class MyActiveEventsController extends Controller
{
    public function __invoke()
    {
        $activeEvents = \App\Models\Event::query()
            ->controllableByUser(auth()->user())
            ->published()
            ->active()
            ->with(['user', 'eventType', 'managers'])
            ->paginate()
            ->through(fn ($event) => EventData::fromModel($event));

        return inertia('Profile/MyActiveEventsPage', [
            'events' => $activeEvents,
            'eventTypes' => EventType::query()->active()->get(),
        ]);
    }
}
