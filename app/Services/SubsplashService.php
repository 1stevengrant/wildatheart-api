<?php

namespace App\Services;

use Cache;
use Illuminate\Support\Facades\Http;

class SubsplashService
{
    public function getAccessToken()
    {

        $SUBSPLASH_CLIENT_ID = config('services.subsplash.client_id');
        $SUBSPLASH_CLIENT_SECRET = config('services.subsplash.client_secret');
        $SUBSPLASH_SCOPE = config('services.subsplash.scope');
        $SUBSPLASH_AUTHENTICATION_TOKEN_URL = config('services.subsplash.authentication_token_url');

        if (Cache::has($SUBSPLASH_CLIENT_ID)) {
            $value = Cache::get($SUBSPLASH_CLIENT_ID);

            return $value;
        }

        $response = Http::asForm()->post($SUBSPLASH_AUTHENTICATION_TOKEN_URL, [
            'grant_type' => 'client_credentials',
            'client_id' => $SUBSPLASH_CLIENT_ID,
            'client_secret' => $SUBSPLASH_CLIENT_SECRET,
            'scope' => "app:{$SUBSPLASH_SCOPE}",
        ]);

        if ($response->successful()) {
            $data = $response->json();
            Cache::put($SUBSPLASH_CLIENT_ID, $data['access_token'], $data['expires_in']);

            return $data['access_token'];
        }

        throw new \Exception('Failed to fetch access token');
    }

    public function subsplashFetch($url)
    {
        if (Cache::has($url)) {
            $value = Cache::get($url);

            return $value;
        }

        $accessToken = $this->getAccessToken();
        $token = 'Bearer ' . $accessToken;

        $response = Http::withHeaders([
            'Accept' => 'application/vnd.api+json',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Authorization' => $token,
            'Cache-Control' => 'no-cache',
        ])->withOptions([
            'mode' => 'cors',
            'credentials' => 'include',
        ])->get($url);

        if ($response->successful()) {
            $json = $response->json();
            Cache::put($url, $json, 600);

            return $json;
        }

        if (isset($response->json()['errors'])) {
            return $response->json();
        }

        // Handle error appropriately
        throw new \Exception('Failed to fetch data from Subsplash API');
    }

    public function getRelatedMediaItems($seriesId)
    {
        if (! $seriesId) {
            return null;
        }

        $subsplashSeries = $this->subsplashFetch("https://core.subsplash.com/media/v1/media-series/{$seriesId}");

        $relatedMediaItemsUrl = $subsplashSeries['_links']['media-items']['href'];

        $subsplashMediaItems = $this->subsplashFetch($relatedMediaItemsUrl);

        return $subsplashMediaItems['_embedded']['media-items'];
    }
}
