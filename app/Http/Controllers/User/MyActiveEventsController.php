<?php

namespace App\Http\Controllers\User;

use App\Models\EventType;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;

class MyActiveEventsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $activeEvents = auth()->user()
            ->events()->active()
            ->with('user')
            ->paginate()
            ->through(fn ($event) => EventResource::make($event)->resolve());

        return inertia('Profile/MyActiveEventsPage', [
            'events' => $activeEvents,
            'eventTypes' => EventType::query()->active()->get(),
        ]);
    }
}
