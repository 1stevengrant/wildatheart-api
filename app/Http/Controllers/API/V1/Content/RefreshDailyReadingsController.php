<?php

namespace App\Http\Controllers\API\V1\Content;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Jobs\Transistor\RefreshDailyReadingsJob;

class RefreshDailyReadingsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        RefreshDailyReadingsJob::dispatch(true)->delay(now()->addMinute());

        return response()->json(['message' => 'Daily readings refreshed']);
    }
}
