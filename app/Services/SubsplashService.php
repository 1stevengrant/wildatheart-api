<?php

namespace App\Services;

use Cache;
use Illuminate\Support\Facades\Http;

class SubsplashService
{
    public function getAccessToken()
    {
        $SUBSPLASH_CLIENT_ID = env('SUBSPLASH_CLIENT_ID');
        $SUBSPLASH_CLIENT_SECRET = env('SUBSPLASH_CLIENT_SECRET');
        $SUBSPLASH_SCOPE = env('SUBSPLASH_SCOPE');
        $SUBSPLASH_AUTHENTICATION_TOKEN_URL = env('SUBSPLASH_AUTHENTICATION_TOKEN_URL');

        $response = Http::asForm()->post('https://core.subsplash.com/tokens/v1/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $SUBSPLASH_CLIENT_ID,
            'client_secret' => $SUBSPLASH_CLIENT_SECRET,
            'scope' => "app:{$SUBSPLASH_SCOPE}",
        ]);

        if ($response->successful()) {
            $data = $response->json();

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

        // Handle error appropriately
        throw new \Exception('Failed to fetch data from Subsplash API');
    }
}
