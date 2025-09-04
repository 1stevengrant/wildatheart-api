<?php

namespace App\Http\Integrations\Transistor\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Illuminate\Support\Facades\Cache;
use Saloon\CachePlugin\Contracts\Driver;
use Saloon\CachePlugin\Traits\HasCaching;
use Saloon\CachePlugin\Contracts\Cacheable;
use Saloon\CachePlugin\Drivers\LaravelCacheDriver;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class GetEpisodesRequest extends Request implements Cacheable, Paginatable
{
    use HasCaching;

    public function __construct(protected string $show_id) {}

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
            'show_id' => $this->show_id,

        ];
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/episodes';
    }
}
