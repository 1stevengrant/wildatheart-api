<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
    public function transistorFetch($url)
    {
        $TRANSISTOR_CACHE_DURATION = config('services.transistor.cache_duration');
        $TRANSISTOR_API_KEY = config('services.transistor.api_key');

        if (Cache::has($url)) {
            $value = Cache::get($url);

            return $value;
        }

        $response = Http::withHeaders([
            'x-api-key' => $TRANSISTOR_API_KEY,
        ])->get($url);

        if ($response->successful()) {
            $json = $response->json();

            if (isset($json['errors'])) {
                return $json;
            }

            Cache::put($url, $json, (int) $TRANSISTOR_CACHE_DURATION);

            return $json;
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

        $url = config('services.transistor.api_url').'episodes/'.$episodeId;

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
        $url = config('services.transistor.api_url').'episodes?show_id='.$showId.'&status=published&pagination[per]='.$count;
        $json = $this->transistorFetch($url);

        if (isset($json['errors']) || ! isset($json['data'])) {
            return $json;
        }

        return $json['data'];
    }

    public function getAll($showId, $status = 'published')
    {

        $url = config('services.transistor.api_url').'episodes?show_id='.$showId.'&status='.$status.'&pagination[per]=100';

        $json = $this->transistorFetch($url);

        if (! empty($json['errors'])) {
            return $json['errors'];
        }

        $data = $json['data'];

        if (isset($json['meta']['currentPage']) && isset($json['meta']['totalPages'])) {
            $currentPage = $json['meta']['currentPage'];

            // Move through all pages of data and merge them into the $data array
            while ($currentPage < $json['meta']['totalPages']) {
                $currentPage += 1;

                $url = config('services.transistor.api_url').'episodes?show_id='.$showId.'&status='.$status.'&pagination[per]=100&pagination[page]='.$currentPage;
                $json = $this->transistorFetch($url);

                if (! isset($json['errors'])) {
                    $data = array_merge($data, $json['data']);
                }
            }
        }

        return $data;
    }
}
