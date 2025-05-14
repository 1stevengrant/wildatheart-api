<?php

namespace App\Http\Integrations\WeGive;

use Redis;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Limit;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\RateLimitPlugin\Stores\RedisStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;

class WeGiveConnector extends Connector
{
    use HasRateLimits;

    public const string ERROR_LOG_NAME = 'wegive_error';

    public const string WARNING_LOG_NAME = 'wegive_warning';

    public const string INFO_LOG_NAME = 'wegive';

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

    protected function resolveLimits(): array
    {
        return [
            Limit::allow(200)->everyMinute(),
        ];
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        $redis = new Redis;
        $redis->connect('127.0.0.1');

        return new RedisStore($redis);
    }
}
