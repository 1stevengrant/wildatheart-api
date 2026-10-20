<?php

namespace App\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use App\Queries\PlatformStatsQuery;
use App\Http\Controllers\Controller;

class StatsController extends Controller
{
    public function __invoke(Request $request, PlatformStatsQuery $platformStats)
    {
        $cacheKey = 'event_stats';

        $eventStats = cache()->remember($cacheKey, now()->addHour(), function () use ($platformStats) {
            return [
                'data' => $platformStats->get(),
            ];
        });

        return response()->json($eventStats);
    }
}
