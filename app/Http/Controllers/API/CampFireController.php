<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

class CampFireController extends Controller
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
            'features' => \App\Models\CampFire::query()->future()->get()->map(function ($location) {
                return [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $location->id,
                        'date' => prettyDate($location->date),
                        'time' => prettyTime($location->time),
                        'description' => $location->description,
                        'state' => $location->state,
                        'city' => $location->city,
                        'attendee_limit' => $location->attendee_limit,
                        'spaces_remaining' => $location->remaining,
                        'url' => route('view.fire', $location->id),
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $location->longitude,
                            $location->latitude,
                        ],
                    ],
                ];
            }),
        ]);
    }
}
