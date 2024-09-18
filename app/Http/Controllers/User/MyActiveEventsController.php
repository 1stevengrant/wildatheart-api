<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\EventType;

class MyActiveEventsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $activeEvents = auth()->user()
            ->events()->active()
            ->with('host')
            ->paginate()
            ->through(fn ($event) => EventResource::make($event)->resolve());

        return inertia('Profile/MyActiveEventsPage', [
            'events' => $activeEvents,
            'eventTypes' => EventType::query()->active()->get(),
        ]);
    }
}
