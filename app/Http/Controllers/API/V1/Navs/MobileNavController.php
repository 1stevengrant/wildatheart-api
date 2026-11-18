<?php

namespace App\Http\Controllers\API\V1\Navs;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\NavigationDataService;
use App\Exceptions\NavigationUnavailableException;

class MobileNavController extends Controller
{
    public function __invoke(NavigationDataService $navigation): JsonResponse
    {
        try {
            $navigationData = $navigation->mobile();
        } catch (NavigationUnavailableException $exception) {
            return response()->json($exception->toErrorPayload(), 404);
        } catch (Exception $exception) {
            Log::error("Navigation API error: {$exception->getMessage()}", [
                'handle' => NavigationDataService::MOBILE_HANDLE,
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'code' => 500,
                'navigation_type' => NavigationDataService::MOBILE_HANDLE,
            ], 500);
        }

        return response()->json($navigationData, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'public, max-age=' . (NavigationDataService::CACHE_MINUTES * 60),
            'X-Navigation-Type' => NavigationDataService::MOBILE_HANDLE,
        ]);
    }
}
