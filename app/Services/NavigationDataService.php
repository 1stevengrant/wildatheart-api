<?php

namespace App\Services;

use Closure;
use Statamic\Facades\GlobalSet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Data\Navigation\MobileNavResponseData;
use App\Data\Navigation\DesktopNavResponseData;
use App\Exceptions\NavigationUnavailableException;

class NavigationDataService
{
    public const int CACHE_MINUTES = 60;

    public const string DESKTOP_HANDLE = 'desktop_nav';

    public const string MOBILE_HANDLE = 'mobile_nav';

    public const string ALL_HANDLE = 'all';

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws NavigationUnavailableException
     */
    public function desktop(): array
    {
        return $this->remember(self::DESKTOP_HANDLE, function (): array {
            $data = $this->globalSetData(self::DESKTOP_HANDLE);

            return DesktopNavResponseData::fromStatamicData($data['desktop_nav'] ?? [])->toResponseArray();
        });
    }

    /**
     * @return array{
     *     main_nav_bar: array<int, array<string, mixed>>,
     *     drawer_items: array<int, array<string, mixed>>
     * }
     *
     * @throws NavigationUnavailableException
     */
    public function mobile(): array
    {
        return $this->remember(self::MOBILE_HANDLE, function (): array {
            $data = $this->globalSetData(self::MOBILE_HANDLE);

            return MobileNavResponseData::fromStatamicData(
                $data['main_nav_bar'] ?? [],
                $data['drawer_items'] ?? [],
            )->toResponseArray();
        });
    }

    /**
     * @return array{
     *     desktop: array<int, array<string, mixed>>,
     *     mobile: array<string, mixed>
     * }
     */
    public function all(): array
    {
        return $this->remember(self::ALL_HANDLE, fn (): array => [
            'desktop' => $this->orEmpty(fn (): array => $this->desktop()),
            'mobile' => $this->orEmpty(fn (): array => $this->mobile()),
        ]);
    }

    /** @return array<int, string> */
    public function forgetCached(): array
    {
        return collect([self::DESKTOP_HANDLE, self::MOBILE_HANDLE, self::ALL_HANDLE])
            ->map(fn (string $handle): string => $this->cacheKey($handle))
            ->filter(fn (string $key): bool => Cache::forget($key))
            ->values()
            ->all();
    }

    /**
     * @param  Closure(): array<mixed>  $callback
     * @return array<mixed>
     */
    private function remember(string $handle, Closure $callback): array
    {
        return Cache::remember($this->cacheKey($handle), now()->addMinutes(self::CACHE_MINUTES), $callback);
    }

    private function cacheKey(string $handle): string
    {
        return "navigation.{$handle}.v3";
    }

    /**
     * @param  Closure(): array<mixed>  $callback
     * @return array<mixed>
     */
    private function orEmpty(Closure $callback): array
    {
        try {
            return $callback();
        } catch (NavigationUnavailableException) {
            return [];
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws NavigationUnavailableException
     */
    private function globalSetData(string $handle): array
    {
        $globalSet = GlobalSet::findByHandle($handle);

        if (! $globalSet) {
            Log::warning("Navigation global set not found: {$handle}");

            throw NavigationUnavailableException::globalSetMissing($handle);
        }

        $variables = $globalSet->inDefaultSite();

        if (! $variables) {
            Log::warning("Navigation variables not found for: {$handle}");

            throw NavigationUnavailableException::variablesMissing($handle);
        }

        $data = $variables->data()->all();

        if (empty($data)) {
            Log::warning("Empty navigation data for: {$handle}");

            throw NavigationUnavailableException::dataEmpty($handle);
        }

        return $data;
    }
}
