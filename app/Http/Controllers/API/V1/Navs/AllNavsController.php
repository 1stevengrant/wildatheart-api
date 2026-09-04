<?php

namespace App\Http\Controllers\API\V1\Navs;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AllNavsController extends BaseNavController
{
    public function __invoke(): JsonResponse
    {
        $cacheKey = 'navigation.all.v2';

        try {
            $navigation = Cache::remember($cacheKey, now()->addMinutes($this->getCacheDuration()), function (): array {
                $desktopController = new DesktopNavController;
                $mobileController = new MobileNavController;

                $desktopData = $this->fetchNavData($desktopController);
                $mobileData = $this->fetchNavData($mobileController);

                return [
                    'desktop' => $desktopData,
                    'mobile' => $mobileData,
                ];
            });

            return response()->json($navigation, 200, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'public, max-age=' . ($this->getCacheDuration() * 60),
                'X-Navigation-Type' => 'all',
            ]);
        } catch (Exception $e) {
            Log::error("All Navigation API error: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'code' => 500,
                'navigation_type' => 'all',
            ], 500);
        }
    }

    protected function getGlobalSetHandle(): string
    {
        return 'all';
    }

    protected function processNavigationData(array $data): array
    {
        return $data;
    }

    /**
     * Fetch navigation data from a controller and extract the JSON data
     */
    private function fetchNavData(BaseNavController $controller): array
    {
        $response = $controller->getNavigationData();

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return json_decode($response->getContent(), true) ?? [];
    }
}
