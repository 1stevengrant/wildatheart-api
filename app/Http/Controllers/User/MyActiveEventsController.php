<?php

namespace App\Http\Controllers\User;

use App\Data\EventData;
use App\Models\EventType;
use App\Http\Controllers\Controller;

class MyActiveEventsController extends Controller
{
    public function __invoke()
    {
        $activeEvents = auth()->user()
            ->events()->active()
            ->with(['user', 'eventType'])
            ->paginate()
            ->through(fn ($event) => EventData::fromModel($event));

        return inertia('Profile/MyActiveEventsPage', [
            'events' => $activeEvents,
            'eventTypes' => EventType::query()->active()->get(),
        ]);
    }
}
