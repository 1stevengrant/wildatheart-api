<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TransistorService
{
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
            $currentPage = $json['meta']['currentPage'];
            while($currentPage <= $json['meta']['totalPages']) {
                $currentPage += 1;
                $response = Http::withHeaders([
                    'x-api-key' => $TRANSISTOR_API_KEY,
                ])->get($url . '&pagination[page]=' . $currentPage);
                if ($response->successful()) {
                    $data = array_merge($data, $response->json()['data']);
                }
           
            }

            Cache::put($url, $json, (int) $TRANSISTOR_CACHE_DURATION);


            return $data;
        }

        if (isset($response->json()['errors'])) {
            return $response->json();
        }

        // Handle error appropriately
        throw new \Exception('Failed to fetch data from Transistor API');
    }

}
