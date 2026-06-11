<?php

namespace App\Services\Auth0;

use Throwable;
use Illuminate\Support\Facades\Http;
use App\DataTransferObjects\Auth0Profile;

class UserinfoClient
{
    /**
     * Fetch the user's profile from Auth0's /userinfo endpoint using their
     * own access token. Returns null on any failure — callers treat the
     * profile as simply unavailable.
     */
    public function fetchProfile(string $accessToken): ?Auth0Profile
    {
        $url = (string) config('auth0.api.userinfo_url');

        if ($url === '') {
            return null;
        }

        try {
            $response = Http::withToken($accessToken)->timeout(5)->get($url);
        } catch (Throwable) {
            return null;
        }

        if ($response->failed()) {
            return null;
        }

        $data = $response->json();

        if (! is_array($data)) {
            return null;
        }

        return Auth0Profile::fromUserinfo($data);
    }
}
