<?php

namespace App\Http\Integrations\Transistor;

use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\RateLimitPlugin\Limit;
use Illuminate\Support\Facades\Cache;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\PaginationPlugin\PagedPaginator;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Stores\LaravelCacheStore;

class TransistorConnector extends Connector implements HasPagination
{
    use AcceptsJson, HasRateLimits;

    protected function resolveLimits(): array
    {
        return [
            Limit::allow(5)->everySeconds(10)->sleep(),
        ];
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        return new LaravelCacheStore(Cache::store('redis'));
    }

    /**
     * The Base URL of the API
     */
    public function resolveBaseUrl(): string
    {
        return config('services.transistor.api_url');
    }

    public function paginate(Request $request): PagedPaginator
    {
        return new class(connector: $this, request: $request) extends PagedPaginator
        {
            protected ?int $perPageLimit = 100;

            protected function isLastPage(Response $response): bool
            {
                return $response->json('meta.currentPage') === $response->json('meta.totalPages');
            }

            protected function getPageItems(Response $response, Request $request): array
            {
                return $response->json('data');
            }

            protected function applyPagination(Request $request): Request
            {
                $request->query()->add('pagination[page]', $this->currentPage);

                if (isset($this->perPageLimit)) {
                    $request->query()->add('pagination[per]', $this->perPageLimit);
                }

                return $request;
            }
        };
    }

    /**
     * Default HTTP client options
     */
    protected function defaultConfig(): array
    {
        return [
            'timeout' => 120,
            'connect_timeout' => 10,
        ];
    }

    protected function defaultAuth(): HeaderAuthenticator
    {
        return new HeaderAuthenticator(config('services.transistor.api_key'), 'x-api-key');
    }
}
