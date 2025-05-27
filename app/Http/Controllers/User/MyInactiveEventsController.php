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
        $events = auth()->user()
            ->events()->inactive()
            ->with(['user', 'eventType'])
            ->paginate()
            ->through(fn ($event) => EventData::fromModel($event));

        return inertia('Profile/MyInactiveEventsPage', [
            'events' => $events,
            'eventTypes' => EventType::query()->active()->get(),
        ]);
    }
}
