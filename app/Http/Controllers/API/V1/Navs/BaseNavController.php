<?php

namespace App\Http\Controllers\API\V1\Navs;

use Exception;
use Statamic\Facades\GlobalSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

abstract class BaseNavController extends Controller
{
    /**
     * Get the global set handle for the navigation
     */
    abstract protected function getGlobalSetHandle(): string;

    /**
     * Process the navigation data before returning
     */
    abstract protected function processNavigationData(array $data): array;

    /**
     * Get additional cache tags for more granular cache invalidation
     */
    protected function getCacheTags(): array
    {
        return ['navigation', $this->getGlobalSetHandle()];
    }

    /**
     * Get the error message when global set is not found
     */
    protected function getNotFoundMessage(): string
    {
        return ucfirst(str_replace('_', ' ', $this->getGlobalSetHandle())) . ' global set not found';
    }

    /**
     * Fetch and process navigation data with caching
     */
    public function getNavigationData(): JsonResponse
    {
        $cacheKey = "navigation.{$this->getGlobalSetHandle()}.v2";

        try {
            $response = Cache::remember($cacheKey, now()->addMinutes($this->getCacheDuration()), function (): array {
                $globalSet = GlobalSet::findByHandle($this->getGlobalSetHandle());

                if (! $globalSet) {
                    Log::warning("Navigation global set not found: {$this->getGlobalSetHandle()}");

                    return $this->errorPayload($this->getNotFoundMessage(), 404);
                }

                // Get the variables (actual content) for the default site
                $variables = $globalSet->inDefaultSite();

                if (! $variables) {
                    Log::warning("Navigation variables not found for: {$this->getGlobalSetHandle()}");

                    return $this->errorPayload('No navigation data found for default site', 404);
                }

                // Get the navigation data
                $data = $variables->data()->all();

                if (empty($data)) {
                    Log::warning("Empty navigation data for: {$this->getGlobalSetHandle()}");

                    return $this->errorPayload('Navigation data is empty', 404);
                }

                // Process the data
                $processedData = $this->processNavigationData($data);

                return $this->successPayload($processedData);
            });

            return response()->json($response['data'], $response['status'], $response['headers']);
        } catch (Exception $e) {
            Log::error("Navigation API error: {$e->getMessage()}", [
                'handle' => $this->getGlobalSetHandle(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Internal server error', 500);
        }
    }

    /**
     * Return consistent error response format
     */
    private function errorResponse(string $message, int $code): JsonResponse
    {
        $response = $this->errorPayload($message, $code);

        return response()->json($response['data'], $response['status']);
    }

    /**
     * @return array{data: array<string, mixed>, status: int, headers: array<string, string>}
     */
    private function errorPayload(string $message, int $code): array
    {
        return [
            'data' => [
                'error' => $message,
                'code' => $code,
                'navigation_type' => $this->getGlobalSetHandle(),
            ],
            'status' => $code,
            'headers' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{data: array<string, mixed>, status: int, headers: array<string, string>}
     */
    private function successPayload(array $data): array
    {
        return [
            'data' => $data,
            'status' => 200,
            'headers' => [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'public, max-age=' . ($this->getCacheDuration() * 60),
                'X-Navigation-Type' => $this->getGlobalSetHandle(),
            ],
        ];
    }

    /**
     * Get cache duration in minutes (can be overridden by child classes)
     */
    protected function getCacheDuration(): int
    {
        return 60; // 1 hour default
    }

    /**
     * Filter items by enabled status
     */
    protected function filterEnabledItems($items): array
    {
        return collect($items)
            ->filter(fn ($item) => $item['enabled'] ?? false)
            ->values()
            ->all();
    }

    /**
     * Filter nested dropdown links by enabled status
     */
    protected function filterNestedDropdownLinks(array $item): array
    {
        if (isset($item['dropdown_links']) && is_array($item['dropdown_links'])) {
            $item['dropdown_links'] = $this->filterEnabledItems($item['dropdown_links']);
        }

        return $item;
    }
}
