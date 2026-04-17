<?php

namespace App\Http\Integrations\WeGive;

use RuntimeException;
use Saloon\Http\Connector;
use Saloon\Http\Auth\TokenAuthenticator;

class WeGiveConnector extends Connector
{
    public const ERROR_LOG_NAME = 'wegive_error';

    public const WARNING_LOG_NAME = 'wegive_warning';

    public const INFO_LOG_NAME = 'wegive';

    public ?int $tries = 1;

    protected function defaultAuth(): TokenAuthenticator
    {
        $apiKey = config('services.wegive.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('WeGive API key is not configured. Set WEGIVE_API_KEY in the environment.');
        }

        return new TokenAuthenticator($apiKey);
    }

    public function resolveBaseUrl(): string
    {
        return 'https://api.wegive.com/api';
    }

    public function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

    }
}
