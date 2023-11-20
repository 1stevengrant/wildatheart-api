<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    public function __invoke()
    {
        return cache()->remember('events', now()->addHour(), function () {
            return response()->json([
                'type' => 'FeatureCollection',
                'features' => $this->getEvents(),
            ]);
        });
    }

    protected function getEvents()
    {
        return Event::query()->public()->with('attendees', 'user', 'host')->get()->map(function ($event) {
            return [
                'type' => 'Feature',
                'properties' => [
                    'id' => $event->id,
                    'state' => getStateAbbreviation($event->state),
                    'city' => $event->city,
                    'title' => $event->title,
                    'location' => $event->location,
                    'country' => $event->country,
                    'url' => route('view.event', $event->id),
                    'host' => $event->user->full_name,
                    'hostProfilePhoto' => $event->hostPhoto(),
                    'image' => asset('img/wah-fires-suburb-1.webp'),
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        $event->longitude,
                        $event->latitude,
                    ],
                ],
            ];
        });
    }
}
