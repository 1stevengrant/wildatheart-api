<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Http\Integrations\Transistor\TransistorConnector;
use App\Http\Integrations\Transistor\Requests\GetEpisodesRequest;

class TransistorService
{
    /**
     * Fetch data from the Transistor API
     *
     * Transistor API is rate limited to 10 requests every 10 seconds, so this method caches the results of the API call.
     * Transistor API is paginated, so this method fetches all pages of data and merges them into a single array.
     *
     * @param  string  $url  The URL to fetch data from
     * @return array The data from the Transistor API
     */
    public function transistorFetch($url, $retry = 0)
    {
        $TRANSISTOR_CACHE_DURATION = config('services.transistor.cache_duration');
        $TRANSISTOR_API_KEY = config('services.transistor.api_key');

        $cacheKey = md5($url);

        if (Cache::has($cacheKey)) {
            $value = Cache::get($cacheKey);

            return $value;
        }

        $response = Http::withHeaders([
            'x-api-key' => $TRANSISTOR_API_KEY,
        ])->get($url);

        if ($response->successful()) {
            $json = $response->json();

            if (! empty($json['errors'])) {
                return $json;
            }

            Cache::put($cacheKey, $json, (int) $TRANSISTOR_CACHE_DURATION);

            return $json;
        }
        // Check for rate limiting (429 Too Many Requests)
        if ($response->status() === 429) {
            if ($retry > 3) {
                throw new \Exception('Rate limit exceeded for Transistor API. Please try again later.');
            }
            // Sleep for 10 seconds
            sleep(10);

            // Retry the request
            return $this->transistorFetch($url, $retry + 1);
        }

        if (isset($response->json()['errors'])) {
            return $response->json();
        }

        // Handle error appropriately
        throw new \Exception('Failed to fetch data from Transistor API');
    }

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
        $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=published&pagination[per]=' . $count;
        $json = $this->transistorFetch($url);

        if (isset($json['errors']) || ! isset($json['data'])) {
            return $json;
        }

        return $json['data'];
    }

    public function getAll($showId, $status = 'published')
    {
        $pageSize = 1200;

        $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=' . $status ;

        $connector = new TransistorConnector;
        $request = new GetEpisodesRequest($showId);
        $request->query()->add('status', $status);
        $allEpisodes = Cache::rememberForever($url, function () use ($request, $connector) {
            $request->disableCaching();
            $paginator = $connector->paginate($request);

            return $paginator->collect()->toArray();
        });
        $recentEpisodes = $connector->send($request);
        // TODO: do a fetch for episodes that were modifed after the last cache.

        $allEpisodesCollection = collect($allEpisodes);
        $allEpisodes = Cache::rememberForever($url, function () use ($allEpisodesCollection, $recentEpisodes) {
            foreach ($recentEpisodes as $episode) {
                $episodeExists = $allEpisodesCollection->contains('id', $episode['id']);
                if (! $episodeExists) {
                    $allEpisodesCollection->push($episode);
                }
            }

            return $allEpisodesCollection->toArray();
        });

        return $allEpisodes;

    }
}
