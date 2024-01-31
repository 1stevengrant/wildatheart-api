<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class MyEventsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $events = auth()->user()->events()->with('host')
            ->paginate()
            ->through(fn ($event) => [
                'id' => $event->id,
                'name' => $event->name,
                'phone' => $event->phone,
                'frequency' => $event->frequency,
                'street_address' => $event->street_address,
                'state' => $event->state,
                'state_code' => $event->state_code,
                'city' => $event->city,
                'zip' => $event->zip,
                'country' => $event->country,
                'longitude' => floatval($event->longitude),
                'latitude' => floatval($event->latitude),
                'region' => $event->region,
                'district' => $event->district,
                'locality' => $event->locality,
                'location' => $event->location,
                'neighborhood' => $event->neighborhood,
                'place' => $event->place,
                'gift_requested' => $event->gift_requested,
                'gift_sent' => $event->gift_sent,
                'host' => $event->host->full_name,
                'approved_at' => $event->approved_at,
                'url' => $event->url,
                'eventType' => $event->eventType ? $event->eventType->name : null,
            ]);

        return inertia('Profile/MyEvents', [
            'events' => $events,
        ]);
    }
}
