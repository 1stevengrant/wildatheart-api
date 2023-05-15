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
            'features' => \App\Models\CampFire::all()->map(function ($location) {
                return [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $location['id'],
                        'location' => $location['address'],
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $location['longitude'],
                            $location['latitude'],
                        ],
                    ],
                ];
            }),
        ]);
    }
}
