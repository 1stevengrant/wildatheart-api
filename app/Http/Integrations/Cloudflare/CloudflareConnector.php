<?php

namespace App\Http\Integrations\Cloudflare;

use Saloon\Http\Connector;
use Saloon\Http\Auth\TokenAuthenticator;

class CloudflareConnector extends Connector
{
    public function resolveBaseUrl(): string
    {
        return 'https://api.cloudflare.com/client/v4';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        $token = config('cloudflare.api_token');

        return new TokenAuthenticator($token);
    }
}
