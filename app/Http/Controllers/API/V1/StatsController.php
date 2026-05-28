<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $cacheKey = 'event_stats';

        $eventStats = cache()->remember($cacheKey, now()->addHour(), function () {
            return [
                'data' => [
                    'retreats' => [
                        [
                            'label' => 'Current Retreat Listings',
                            'count' => Event::query()->retreats()->upcoming()->count(),
                        ],
                        [
                            'label' => 'Retreats in the past year',
                            'count' => Event::query()->retreats()->startedInTheLastYear()->count(),
                        ],
                        [
                            'label' => 'Countries with Retreats',
                            'count' => Event::query()->retreats()->distinct('country')->count(),
                        ],
                        [
                            'label' => 'All Time Retreats',
                            'count' => Event::query()->retreats()->count(),
                        ],
                    ],
                    'experiences' => [
                        [
                            'label' => 'Current Experience Listings',
                            'count' => Event::query()->experiences()->upcoming()->count(),
                        ],
                        [
                            'label' => 'Experiences in the past year',
                            'count' => Event::query()->experiences()->startedInTheLastYear()->count(),
                        ],
                        [
                            'label' => 'Countries with Experiences',
                            'count' => Event::query()->experiences()->distinct('country')->count(),
                        ],
                        [
                            'label' => 'All Time Experiences',
                            'count' => Event::query()->experiences()->count(),
                        ],
                    ],
                    'fires' => [
                        [
                            'label' => 'Current Fire Listings',
                            'count' => Event::query()->fires()->published()->count(),
                        ],
                        [
                            'label' => 'Countries with Fires',
                            'count' => Event::query()->fires()->published()->distinct('country')->count(),
                        ],
                        [
                            'label' => 'All Time Fires',
                            'count' => Event::query()->fires()->count(),
                        ],
                        [
                            'label' => 'Fires run in the past year',
                            'count' => Event::query()->fires()->published()->active()->count(),
                        ],
                        [
                            'label' => 'Countries with current fires',
                            'count' => Event::query()->fires()->published()->active()->distinct('country')->count(),
                        ],
                    ],

                ],
            ];
        });

        return response()->json($eventStats);
    }
}
