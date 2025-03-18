<?php

namespace App\Http\Integrations\WeGive;

use Saloon\Http\Connector;
use Saloon\Http\Auth\TokenAuthenticator;

class WeGiveConnector extends Connector
{
    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator(config('services.wegive.api_key'));
    }

    public function resolveBaseUrl(): string
    {
        return 'https://api.wegive.com/api/dashboard';
    }

    public function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

    }
}
