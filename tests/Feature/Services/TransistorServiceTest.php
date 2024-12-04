<?php

namespace Tests\Feature\Services;

use App\Services\TransistorService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    Cache::flush();
    $this->transistorService = new TransistorService;
});

it('returns cached data when available', function () {
    $url = 'https://api.transistor.fm/v1/episodes';
    $cacheKey = md5($url);
    $cachedData = ['data' => 'cached'];

    Cache::shouldReceive('has')
        ->once()
        ->with($cacheKey)
        ->andReturn(true);

    Cache::shouldReceive('get')
        ->once()
        ->with($cacheKey)
        ->andReturn($cachedData);

    $result = $this->transistorService->transistorFetch($url);

    expect($result)->toBe($cachedData);
});

it('fetches from API and caches data when not cached', function () {
    $url = 'https://api.transistor.fm/v1/episodes';
    $cacheKey = md5($url);
    $apiResponse = ['data' => 'from_api'];
    $cacheDuration = (int) config('services.transistor.cache_duration');

    Cache::shouldReceive('has')
        ->once()
        ->with($cacheKey)
        ->andReturn(false);

    Http::fake([
        $url => Http::response($apiResponse, 200),
    ]);

    Cache::shouldReceive('put')
        ->once()
        ->with($cacheKey, $apiResponse, $cacheDuration);

    $result = $this->transistorService->transistorFetch($url);

    expect($result)->toBe($apiResponse);
});

it('returns errors when API responds with errors', function () {
    $url = 'https://api.transistor.fm/v1/episodes';
    $cacheKey = md5($url);
    $apiResponse = ['errors' => ['message' => 'Something went wrong']];

    Cache::shouldReceive('has')
        ->once()
        ->with($cacheKey)
        ->andReturn(false);

    Http::fake([
        $url => Http::response($apiResponse, 200),
    ]);

    $result = $this->transistorService->transistorFetch($url);

    expect($result)->toBe($apiResponse);
});

it('throws an exception when API request fails', function () {
    $url = 'https://api.transistor.fm/v1/episodes';
    $cacheKey = md5($url);
    Cache::shouldReceive('has')
        ->once()
        ->with($cacheKey)
        ->andReturn(false);

    Http::fake([
        $url => Http::response(null, 500),
    ]);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Failed to fetch data from Transistor API');

    $this->transistorService->transistorFetch($url);
});

it('fetches recent episodes successfully', function () {
    $showId = '12345';
    $count = 2;
    $expectedData = ['data' => ['episode1', 'episode2']];

    Http::fake([
        '*' => Http::response($expectedData, 200),
    ]);

    $result = $this->transistorService->getRecent($showId, $count);

    expect($result)->toBe($expectedData['data']);
});

it('returns errors when API responds with errors in getRecent', function () {
    $showId = '12345';
    $count = 2;
    $errors = ['errors' => ['message' => 'Something went wrong']];

    Http::fake([
        '*' => Http::response($errors, 200),
    ]);

    $result = $this->transistorService->getRecent($showId, $count);

    expect($result)->toBe($errors);
});

it('returns data from getAll', function () {
    $showId = '12345';
    $data = ['data' => ['episode1', 'episode2']];
    $url = config('services.transistor.api_url').'episodes?show_id=12345&status=draft&pagination%5Bper%5D=1200';

    Http::fake(function (Request $request) use ($url, $data) {
        if ($request->url() === $url) {
            return Http::response($data, 200);
        } else {
            return Http::response(['errors' => [['title' => 'Resource not found']]], 200);
        }
    });

    $result = $this->transistorService->getAll($showId, 'draft');

    expect($result)->toBe($data['data']);
});

it('Requests all pages if more pags are available', function () {
    $showId = '12345';

    $firstPage = ['meta' => ['currentPage' => 1, 'totalPages' => 2], 'data' => ['episode1', 'episode2']];
    $secondPage = ['meta' => ['currentPage' => 2, 'totalPages' => 2], 'data' => ['episode3', 'episode4']];

    $url = config('services.transistor.api_url').'episodes?show_id=12345&status=draft&pagination%5Bper%5D=1200';

    Http::fake(function (Request $request) use ($url, $firstPage, $secondPage) {
        if ($request->url() === $url) {
            return Http::response($firstPage, 200);
        } elseif ($request->url() === $url.'&pagination%5Bpage%5D=2') {
            return Http::response($secondPage, 200);
        } else {
            return Http::response(['errors' => [['title' => 'Resource not found']]], 200);
        }
    });

    $result = $this->transistorService->getAll($showId, 'draft');
    Http::assertSentCount(2);

    expect($result)->toBe(array_merge($firstPage['data'], $secondPage['data']));
});
