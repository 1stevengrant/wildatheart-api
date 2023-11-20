<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MyConnectionsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $eventConnections = auth()->user()
            ->events()
            ->with('attendees')
            ->paginate(100)
            ->through(fn ($event) => [
                'id' => $event->id,
                'name' => $event->name,
                'attendees' => $event->attendees->map(fn ($attendee) => [
                    'family_name' => $attendee->family_name,
                    'given_name' => $attendee->given_name,
                    'phone' => $attendee->phone,
                    'email' => $attendee->email,
                    'contacted_on' => $attendee->contacted_on,
                    'city' => $attendee->city,
                    'state' => $attendee->state,
                    'country' => $attendee->country,
                ]),
            ]);

        return inertia('Profile/MyConnections', [
            'eventConnections' => $eventConnections,
            'allConnections' => auth()->user()->generalConnections->sortBy('family_name'),
        ]);
    }
}
