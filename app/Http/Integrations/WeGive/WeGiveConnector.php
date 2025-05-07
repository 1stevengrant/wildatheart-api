<?php

namespace App\Http\Integrations\WeGive;

use Saloon\Http\Connector;
use Saloon\Http\Auth\TokenAuthenticator;

class WeGiveConnector extends Connector
{
    public const ERROR_LOG_NAME = 'wegive_error';

    public const WARNING_LOG_NAME = 'wegive_warning';

    public const INFO_LOG_NAME = 'wegive';

    public ?int $tries = 3;

    public ?int $retryInterval = 5000;

    protected function defaultAuth(): TokenAuthenticator
    {
        return new TokenAuthenticator(config('services.wegive.api_key'));
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
