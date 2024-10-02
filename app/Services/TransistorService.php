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
     * @param string $url The URL to fetch data from
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

            $data = $json['data'];

            if(isset($json['meta']['currentPage']) && isset($json['meta']['totalPages'])) {
                $currentPage = $json['meta']['currentPage'];

                // Move through all pages of data and merge them into the $data array
                while($currentPage <= $json['meta']['totalPages']) {
                    $currentPage += 1;
                    $response = Http::withHeaders([
                        'x-api-key' => $TRANSISTOR_API_KEY,
                    ])->get($url . '&pagination[page]=' . $currentPage);
                    if ($response->successful()) {
                        $data = array_merge($data, $response->json()['data']);
                    }
            
                }
            }

            Cache::put($url, $data, (int) $TRANSISTOR_CACHE_DURATION);


            return $data;
        }

        if (isset($response->json()['errors'])) {
            return $response->json();
        }

        // Handle error appropriately
        throw new \Exception('Failed to fetch data from Transistor API');
    }

}
