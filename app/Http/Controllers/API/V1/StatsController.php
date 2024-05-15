<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Handle the incoming request.
     */
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
                            'count' => Event::query()->retreats()->inTheLastYear()->count(),
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
                            'count' => Event::query()->experiences()->inTheLastYear()->count(),
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
                            'label' => 'Current Fires Listings',
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
                    ],

                ],
            ];
        });

        return response()->json($eventStats);
    }
}
