<?php

namespace App\Http\Integrations\Auth0;

use Saloon\Http\Connector;
use Illuminate\Support\Facades\Http;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Auth\TokenAuthenticator;

class Auth0Connector extends Connector
{
    use AcceptsJson;

    protected ?string $accessToken = null;

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return config('auth0.management.audience');
    }

    /**
     * Default headers for every request
     */
    public function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [];
    }

    /**
     * Default authentication for requests
     */
    protected function defaultAuth(): TokenAuthenticator
    {

        $accessToken = $this->getAccessToken();

        return new TokenAuthenticator($accessToken);
    }

    protected function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $tokenEndpoint = config('auth0.management.endpoint');

        $response = Http::post($tokenEndpoint, [
            'client_id' => config('auth0.management.client_id'),
            'client_secret' => config('auth0.management.client_secret'),
            'audience' => config('auth0.management.audience'),
            'grant_type' => 'client_credentials',
        ]);

        if (! $response->successful()) {
            throw new \Exception('Failed to obtain Auth0 access token: ' . $response->body());
        }

        $data = $response->json();
        $this->accessToken = $data['access_token'];

        return $this->accessToken;
    }
}
