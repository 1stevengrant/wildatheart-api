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
use Saloon\Exceptions\Request\ServerException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Helpers\RetryAfterHelper;
use Saloon\RateLimitPlugin\Stores\LaravelCacheStore;

class TransistorConnector extends Connector implements HasPagination
{
    use AcceptsJson, HasRateLimits;

    public ?int $tries = 3;

    public ?int $retryInterval;

    public ?bool $useExponentialBackoff = true;

    public function __construct()
    {
        $this->retryInterval = config('services.transistor.retry_interval');
    }

    public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool
    {
        return $exception instanceof FatalRequestException
            || $exception instanceof ServerException;
    }

    protected function resolveLimits(): array
    {
        return [
            Limit::allow(5)->everySeconds(10)->sleep(),
        ];
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        return new LaravelCacheStore(Cache::store(config('services.transistor.rate_limit_cache_store', 'redis')));
    }

    protected function handleTooManyAttempts(Response $response, Limit $limit): void
    {
        if ($response->status() !== 429) {
            return;
        }

        $limit->exceeded(
            releaseInSeconds: max(10, RetryAfterHelper::parse($response->header('Retry-After')) ?? 10),
        );
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
                $request->query()->add('pagination[page]', $this->currentPage + 1);

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
