<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campfire;

class CampfireController extends Controller
{
    public function __invoke()
    {
        return response()->json(
            [
                'type' => 'FeatureCollection',
                'features' => $this->getCampfires(),
            ],
        );
    }

    protected function getCampfires()
    {
        return Campfire::query()->public()->get()->map(function ($campfire) {
            return [
                'type' => 'Feature',
                'properties' => [
                    'id' => $campfire->id,
                    'state' => getStateAbbreviation($campfire->state),
                    'city' => $campfire->city,
                    'title' => $campfire->title,
                    'location' => $campfire->location,
                    'country' => $campfire->country,
                    'attendee_limit' => $campfire->attendee_limit,
                    'spaces_remaining' => $campfire->remaining,
                    'url' => route('view.campfire', $campfire->id),
                    'host' => $campfire->user->full_name,
                    'hostProfilePhoto' => $campfire->hostPhoto(),
                    'image' => asset('img/wah-fires-suburb-1.webp'),
                    'frequency' => $campfire->frequency,
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        $campfire->longitude,
                        $campfire->latitude,
                    ],
                ],
            ];
        });
    }
}
