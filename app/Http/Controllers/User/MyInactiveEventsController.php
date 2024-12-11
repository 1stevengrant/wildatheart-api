<?php

namespace App\Http\Controllers\User;

use App\Models\EventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;

class MyInactiveEventsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $events = auth()->user()
            ->events()->inactive()
            ->with('host')
            ->paginate()
            ->through(fn ($event) => EventResource::make($event)->resolve());

        return inertia('Profile/MyInactiveEventsPage', [
            'events' => $events,
            'eventTypes' => EventType::query()->active()->get(),
        ]);
    }
}
