<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class CampfireController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'type' => 'FeatureCollection',
            'crs' => [
                'type' => 'name',
                'properties' => [
                    'name' => 'urn:ogc:def:crs:OGC:1.3:CRS84',
                ],
            ],
            'features' => \App\Models\Campfire::query()->future()->get()->map(function ($campfire) {
                return [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $campfire->id,
                        'date' => prettyDate($campfire->date),
                        'time' => prettyTime($campfire->time),
                        'description' => $campfire->description,
                        'state' => $campfire->state,
                        'city' => $campfire->city,
                        'attendee_limit' => $campfire->attendee_limit,
                        'spaces_remaining' => $campfire->remaining,
                        'url' => route('view.campfire', $campfire->id),
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $campfire->longitude,
                            $campfire->latitude,
                        ],
                    ],
                ];
            }),
        ]);
    }
}
