<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\EventType;

class MyEventsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $events = auth()->user()->events()->with('host')
            ->paginate()
            ->through(fn ($event) => EventResource::make($event)->resolve());

        return inertia('Profile/MyEventsPage', [
            'events' => $events,
            'eventTypes' => EventType::query()->active()->get(),
        ]);
    }
}
