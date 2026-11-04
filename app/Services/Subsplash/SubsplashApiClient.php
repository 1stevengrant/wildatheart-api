<?php

namespace App\Services\Subsplash;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;

class SubsplashApiClient
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.subsplash.base_url');
    }

    public function getAccessToken(): string
    {
        $clientId = config('services.subsplash.client_id');
        $clientSecret = config('services.subsplash.client_secret');
        $scope = config('services.subsplash.scope');
        $tokenUrl = config('services.subsplash.authentication_token_url');

        $cached = Cache::get($clientId);
        if ($cached !== null) {
            return $cached;
        }

        $response = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => "app:{$scope}",
        ]);

        if ($response->successful()) {
            $data = $response->json();
            Cache::put($clientId, $data['access_token'], $data['expires_in'] - 30);

            return $data['access_token'];
        }

        throw new Exception('Failed to fetch access token');
    }

    public function fetch(string $url): ?array
    {
        $cacheDuration = config('services.subsplash.cache_duration');

        $cacheKey = md5($url);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $accessToken = $this->getAccessToken();

        $response = Http::retry(
            [100, 500, 1000],
            when: fn ($exception) => $exception instanceof RequestException
                ? $exception->response->serverError()
                : $exception instanceof ConnectionException,
            throw: false,
        )->withHeaders([
            'Accept' => 'application/vnd.api+json',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Authorization' => "Bearer {$accessToken}",
            'Cache-Control' => 'no-cache',
        ])->withOptions([
            'mode' => 'cors',
            'credentials' => 'include',
        ])->get($url);

        if ($response->status() === 401) {
            Cache::forget(config('services.subsplash.client_id'));
            $accessToken = $this->getAccessToken();

            $response = Http::withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Authorization' => "Bearer {$accessToken}",
                'Cache-Control' => 'no-cache',
            ])->get($url);
        }

        if ($response->successful()) {
            $json = $response->json();
            Cache::put($cacheKey, $json, (int) $cacheDuration);

            return $json;
        }

        if ($response->notFound()) {
            return null;
        }

        throw new Exception("Request to Subsplash API failed with {$response->status()} after three attempts: {$url}");
    }

    public function getMediaItem(string $mediaItemId): ?array
    {
        return $this->fetch("{$this->baseUrl}/media/v1/media-items/{$mediaItemId}");
    }

    public function findMediaItemByTitle(string $title): ?array
    {
        return $this->fetch("{$this->baseUrl}/media/v1/media-items?filter%5Btitle%5D=*" . urlencode($title) . '*');
    }

    public function getPublishedMediaItemsUrl(int $pageSize = 500): string
    {
        $scope = config('services.subsplash.scope');

        return "{$this->baseUrl}/media/v1/media-items?filter%5Bstatus%5D=published&filter%5Bapp_key%5D={$scope}&page%5Bsize%5D={$pageSize}";
    }

    public function getMediaSeries(string $mediaSeriesId): ?array
    {
        return $this->fetch("{$this->baseUrl}/media/v1/media-series/{$mediaSeriesId}");
    }

    public function getMediaSeriesFromItemId(string $mediaItemId): ?string
    {
        $mediaItem = $this->getMediaItem($mediaItemId);

        return Arr::get($mediaItem, '_embedded.media-series.id', null);
    }

    public function getRelatedMediaItems(string $seriesId): ?array
    {
        $subsplashSeries = $this->getMediaSeries($seriesId);

        if (! $subsplashSeries) {
            return null;
        }

        $relatedMediaItemsUrl = $subsplashSeries['_links']['media-items']['href'];
        $subsplashMediaItems = $this->fetch($relatedMediaItemsUrl);

        return $subsplashMediaItems['_embedded']['media-items'];
    }

    public function getAllRelatedMediaItems(string $seriesId): ?array
    {
        $subsplashSeries = $this->getMediaSeries($seriesId);

        if (! $subsplashSeries) {
            return null;
        }

        $relatedMediaItemsUrl = $subsplashSeries['_links']['media-items']['href'];
        $subsplashMediaItems = $this->fetch($relatedMediaItemsUrl);
        $next = data_get($subsplashMediaItems, '_links.next.href');
        $mediaItems = data_get($subsplashMediaItems, '_embedded.media-items', []);

        while ($next) {
            $subsplashMediaItems = $this->fetch($next);
            $next = data_get($subsplashMediaItems, '_links.next.href');
            $mediaItems = array_merge($mediaItems, data_get($subsplashMediaItems, '_embedded.media-items', []));
        }

        return $mediaItems;
    }

    public function getListRows(string $listId): ?array
    {
        return $this->fetch("{$this->baseUrl}/builder/v1/list-rows?filter%5Bsource_list%5D={$listId}");
    }
}
