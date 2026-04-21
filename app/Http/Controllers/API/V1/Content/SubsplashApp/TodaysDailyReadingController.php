<?php

namespace App\Http\Controllers\API\V1\Content\SubsplashApp;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\SubsplashAppService;

class TodaysDailyReadingController extends Controller
{
    public function __construct(private readonly SubsplashAppService $subsplashAppService) {}

    public function __invoke(): JsonResponse
    {
        return response()->json($this->subsplashAppService->getDailyReadingToday());
    }
}
