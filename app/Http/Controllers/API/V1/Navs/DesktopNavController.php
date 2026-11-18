<?php

namespace App\Http\Controllers\API\V1\Navs;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\NavigationDataService;
use App\Exceptions\NavigationUnavailableException;

class DesktopNavController extends Controller
{
    public function __invoke(NavigationDataService $navigation): JsonResponse
    {
        try {
            $items = $navigation->desktop();
        } catch (NavigationUnavailableException $exception) {
            return response()->json($exception->toErrorPayload(), 404);
        } catch (Exception $exception) {
            Log::error("Navigation API error: {$exception->getMessage()}", [
                'handle' => NavigationDataService::DESKTOP_HANDLE,
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'code' => 500,
                'navigation_type' => NavigationDataService::DESKTOP_HANDLE,
            ], 500);
        }

        return response()->json($items, 200, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'public, max-age=' . (NavigationDataService::CACHE_MINUTES * 60),
            'X-Navigation-Type' => NavigationDataService::DESKTOP_HANDLE,
        ]);
    }
}
