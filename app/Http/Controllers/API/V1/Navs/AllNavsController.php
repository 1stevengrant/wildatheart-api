<?php

namespace App\Http\Controllers\API\V1\Navs;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\NavigationDataService;

class AllNavsController extends Controller
{
    public function __invoke(NavigationDataService $navigation): JsonResponse
    {
        try {
            $navigationData = $navigation->all();
        } catch (Exception $exception) {
            Log::error("All Navigation API error: {$exception->getMessage()}", [
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'code' => 500,
                'navigation_type' => NavigationDataService::ALL_HANDLE,
            ], 500);
        }

        return response()->json($navigationData, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'public, max-age=' . (NavigationDataService::CACHE_MINUTES * 60),
            'X-Navigation-Type' => NavigationDataService::ALL_HANDLE,
        ]);
    }
}
