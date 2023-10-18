<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MyConnectionsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $fireConnections = auth()->user()
            ->campfires()
            ->with('attendees')
            ->paginate(100)
            ->through(fn ($campfire) => [
                'id' => $campfire->id,
                'name' => $campfire->name,
                'attendees' => $campfire->attendees->map(fn ($attendee) => [
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
            'fireConnections' => $fireConnections,
            'allConnections' => auth()->user()->generalConnections->sortBy('family_name'),
        ]);
    }
}
