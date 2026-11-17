<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Saloon\Exceptions\Request\ServerException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\Transistor\TransistorConnector;
use Saloon\RateLimitPlugin\Exceptions\RateLimitReachedException;
use App\Http\Integrations\Transistor\Requests\GetEpisodesRequest;
use App\Http\Integrations\Transistor\Exceptions\InvalidJsonResponseException;

class TransistorService
{
    private const REFRESH_LOCK_TTL = 60;

    private const REFRESH_LOCK_WAIT = 10;

    public function updateEpisode($episodeId, $data)
    {
        $TRANSISTOR_API_KEY = config('services.transistor.api_key');

        $url = config('services.transistor.api_url') . 'episodes/' . $episodeId;

        $response = Http::withHeaders([
            'x-api-key' => $TRANSISTOR_API_KEY,
        ])->patch($url, $data);

        if ($response->successful()) {
            $json = $response->json();

            if (isset($json['errors'])) {
                return $json;
            }

            return $json;
        }

        if (isset($response->json()['errors'])) {
            return $response->json();
        }

        // Handle error appropriately
        throw new \Exception('Failed to update episode in Transistor API');
    }

    /**
     * Get the most recent episodes from the Transistor API
     *
     * @param  string  $showId  The ID of the show to fetch episodes from
     * @param  int  $count  The number of episodes to fetch
     * @return array The most recent episodes from the Transistor API
     */
    public function getRecent($showId, $count = 1)
    {
        return collect($this->getAll($showId, 'published'))->take($count)->toArray();
    }

    public function getEpisode($id, $showId)
    {
        $url = config('services.transistor.api_url') . 'episodes/' . $id;

        if (Cache::has($url)) {
            return Cache::get($url);
        }

        $allEpisodes = $this->getAll($showId);
        $episode = Cache::remember($url, 60, function () use ($allEpisodes, $id) {
            return collect($allEpisodes)->firstWhere('id', $id);
        });

        return $episode;
    }

    /**
     * Get all episodes from the Transistor API
     *
     * @param  string  $showId  The ID of the show to fetch episodes from
     * @param  string  $status  The status of the episodes to get.
     * @return array All episodes from the Transistor API
     */
    public function getAll($showId, $status = 'published')
    {
        $url = $this->cacheKey($showId, $status);

        if (Cache::has($url)) {
            return Cache::get($url);
        }

        try {
            Cache::lock($this->lockKey($showId, $status), self::REFRESH_LOCK_TTL)
                ->block(self::REFRESH_LOCK_WAIT, function () use ($showId, $status, $url) {
                    // Another process may have populated the cache while we waited.
                    if (Cache::has($url)) {
                        return;
                    }

                    $this->performRefresh($showId, $status);
                });
        } catch (LockTimeoutException $e) {
            Log::warning('Transistor refresh lock timeout; serving stale or empty data.', [
                'show_id' => $showId,
                'status' => $status,
            ]);
        } catch (RateLimitReachedException $e) {
            Log::warning('Transistor rate limit reached during refresh; serving stale or empty data.', [
                'show_id' => $showId,
                'status' => $status,
            ]);
        } catch (ServerException|FatalRequestException|InvalidJsonResponseException $e) {
            Log::warning('Transistor API unreachable during refresh; serving stale or empty data.', [
                'show_id' => $showId,
                'status' => $status,
                'exception' => $e->getMessage(),
            ]);
        }

        return Cache::get($url) ?? [];
    }

    /**
     * Refresh the episode list from the Transistor API
     */
    public function refreshEpisodeList($showId, $status = 'published')
    {
        try {
            Cache::lock($this->lockKey($showId, $status), self::REFRESH_LOCK_TTL)
                ->block(self::REFRESH_LOCK_WAIT, function () use ($showId, $status) {
                    $this->performRefresh($showId, $status);
                });
        } catch (LockTimeoutException $e) {
            Log::warning('Transistor refresh lock timeout; keeping existing cache.', [
                'show_id' => $showId,
                'status' => $status,
            ]);
        } catch (ServerException|FatalRequestException|InvalidJsonResponseException $e) {
            Log::warning('Transistor API unreachable during refresh; keeping existing cache.', [
                'show_id' => $showId,
                'status' => $status,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    private function performRefresh(string $showId, string $status): void
    {
        Log::info('Refreshing episode list for showId: ' . $showId . ' and status: ' . $status);

        $connector = new TransistorConnector;
        $request = new GetEpisodesRequest($showId);
        $request->query()->add('status', $status);
        $paginator = $connector->paginate($request);
        $episodes = $paginator->collect()->toArray();
        Cache::forever($this->cacheKey($showId, $status), $episodes);
    }

    private function cacheKey(string $showId, string $status): string
    {
        return config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=' . $status;
    }

    private function lockKey(string $showId, string $status): string
    {
        return "transistor-refresh:{$showId}:{$status}";
    }
}
