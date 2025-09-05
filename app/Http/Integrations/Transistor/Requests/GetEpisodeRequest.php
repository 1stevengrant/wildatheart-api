<?php

namespace App\Http\Integrations\Transistor\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;

class GetEpisodeRequest extends Request implements Cacheable
{
    use HasCaching;

    public function __construct(protected ?string $episode_id = null) {}

    public function cacheExpiryInSeconds(): int
    {
        return 300; // 5 minutes
    }

    public function resolveCacheDriver(): Driver
    {
        return new LaravelCacheDriver(Cache::store('redis'));
    }

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    protected function defaultQuery(): array
    {
        return [
        ];
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/episodes' . ($this->episode_id ? '/' . $this->episode_id : '');
    }
}
