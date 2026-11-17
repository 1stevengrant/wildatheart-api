<?php

namespace Tests\Feature\Services;

use Illuminate\Support\Sleep;
use Saloon\Http\PendingRequest;
use Saloon\RateLimitPlugin\Limit;
use Saloon\Http\Faking\MockClient;
use App\Services\TransistorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Saloon\Http\Faking\MockResponse;
use Illuminate\Support\Facades\Cache;
use Saloon\RateLimitPlugin\Exceptions\RateLimitReachedException;
use App\Http\Integrations\Transistor\Requests\GetEpisodesRequest;

beforeEach(function () {
    Cache::flush();

    config([
        'services.transistor.api_key' => 'test-api-key',
        'services.transistor.api_url' => 'https://api.transistor.fm/v1/',
        'services.transistor.cache_duration' => 3600,
        'services.transistor.retry_interval' => 0,
        'services.transistor.rate_limit_cache_store' => 'array',
    ]);

    $this->transistorService = new TransistorService;
});

afterEach(function () {
    MockClient::destroyGlobal();
});

it('returns cached data from getAll when available', function () {
    $showId = '12345';
    $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=published';
    $episodes = [
        ['id' => '1', 'type' => 'episode', 'attributes' => ['title' => 'Episode 1']],
        ['id' => '2', 'type' => 'episode', 'attributes' => ['title' => 'Episode 2']],
    ];

    Cache::forever($url, $episodes);

    $result = $this->transistorService->getAll($showId);

    expect($result)->toBe($episodes);
});

it('fetches from API and caches when not cached', function () {
    $showId = '12345';
    $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=published';
    $episodes = [
        ['id' => '1', 'type' => 'episode', 'attributes' => ['title' => 'Episode 1']],
        ['id' => '2', 'type' => 'episode', 'attributes' => ['title' => 'Episode 2']],
    ];

    $mockClient = MockClient::global([
        GetEpisodesRequest::class => MockResponse::make([
            'data' => $episodes,
            'meta' => ['currentPage' => 1, 'totalPages' => 1],
        ]),
    ]);

    $result = $this->transistorService->getAll($showId);

    $mockClient->assertSent(GetEpisodesRequest::class);
    expect($result)->toBe($episodes);
    expect(Cache::get($url))->toBe($episodes);
});

it('fetches recent episodes', function () {
    $showId = '12345';
    $episodes = [
        ['id' => '1', 'type' => 'episode', 'attributes' => ['title' => 'Episode 1']],
        ['id' => '2', 'type' => 'episode', 'attributes' => ['title' => 'Episode 2']],
        ['id' => '3', 'type' => 'episode', 'attributes' => ['title' => 'Episode 3']],
    ];

    MockClient::global([
        GetEpisodesRequest::class => MockResponse::make([
            'data' => $episodes,
            'meta' => ['currentPage' => 1, 'totalPages' => 1],
        ]),
    ]);

    $result = $this->transistorService->getRecent($showId, 2);

    expect($result)->toHaveCount(2);
    expect($result[0]['id'])->toBe('1');
    expect($result[1]['id'])->toBe('2');
});

it('returns single episode from getEpisode', function () {
    $showId = '12345';
    $episodes = [
        ['id' => '1', 'type' => 'episode', 'attributes' => ['title' => 'Episode 1']],
        ['id' => '2', 'type' => 'episode', 'attributes' => ['title' => 'Episode 2']],
    ];

    MockClient::global([
        GetEpisodesRequest::class => MockResponse::make([
            'data' => $episodes,
            'meta' => ['currentPage' => 1, 'totalPages' => 1],
        ]),
    ]);

    $result = $this->transistorService->getEpisode('2', $showId);

    expect($result)->toBe(['id' => '2', 'type' => 'episode', 'attributes' => ['title' => 'Episode 2']]);
});

it('paginates through all pages', function () {
    $showId = '12345';

    $mockClient = MockClient::global([
        MockResponse::make([
            'data' => [['id' => '1'], ['id' => '2']],
            'meta' => ['currentPage' => 1, 'totalPages' => 2],
        ]),
        MockResponse::make([
            'data' => [['id' => '3'], ['id' => '4']],
            'meta' => ['currentPage' => 2, 'totalPages' => 2],
        ]),
    ]);

    $result = $this->transistorService->getAll($showId);

    $mockClient->assertSentCount(2, GetEpisodesRequest::class);
    expect($result)->toHaveCount(4);
    expect($result[0]['id'])->toBe('1');
    expect($result[3]['id'])->toBe('4');
});

it('requests each page exactly once starting from page one', function () {
    $requestedPages = [];

    MockClient::global([
        GetEpisodesRequest::class => function (PendingRequest $pendingRequest) use (&$requestedPages) {
            $page = $pendingRequest->query()->get('pagination[page]');
            $requestedPages[] = $page;

            return MockResponse::make([
                'data' => [['id' => (string) $page]],
                'meta' => ['currentPage' => $page, 'totalPages' => 3],
            ]);
        },
    ]);

    $result = $this->transistorService->getAll('12345');

    expect($requestedPages)->toBe([1, 2, 3]);
    expect(array_column($result, 'id'))->toBe(['1', '2', '3']);
});

it('updates an episode successfully', function () {
    $episodeId = '123';
    $data = ['episode' => ['title' => 'Updated Title']];

    Http::fake([
        'https://api.transistor.fm/v1/episodes/*' => Http::response([
            'data' => ['id' => '123', 'attributes' => ['title' => 'Updated Title']],
        ], 200),
    ]);

    $result = $this->transistorService->updateEpisode($episodeId, $data);

    expect($result)->toHaveKey('data');
    expect($result['data']['attributes']['title'])->toBe('Updated Title');
});

it('returns errors from updateEpisode when API responds with errors', function () {
    $episodeId = '123';
    $data = ['episode' => ['title' => 'Updated Title']];

    Http::fake([
        'https://api.transistor.fm/v1/episodes/*' => Http::response([
            'errors' => [['title' => 'Validation failed']],
        ], 200),
    ]);

    $result = $this->transistorService->updateEpisode($episodeId, $data);

    expect($result)->toHaveKey('errors');
});

it('throws exception when updateEpisode fails', function () {
    $episodeId = '123';
    $data = ['episode' => ['title' => 'Updated Title']];

    Http::fake([
        'https://api.transistor.fm/v1/episodes/*' => Http::response(null, 500),
    ]);

    $this->transistorService->updateEpisode($episodeId, $data);
})->throws(\Exception::class, 'Failed to update episode in Transistor API');

it('returns empty array and swallows rate limit exception on getAll', function () {
    $showId = 'rate-limited-show';

    MockClient::global([
        GetEpisodesRequest::class => function () {
            throw new RateLimitReachedException(
                Limit::allow(5)->everySeconds(10)->name('too_many_attempts_limit')
            );
        },
    ]);

    $result = $this->transistorService->getAll($showId);

    expect($result)->toBe([]);
});

it('retries transient 5xx failures and ultimately succeeds', function () {
    $showId = 'flaky-show';
    $episodes = [['id' => '99', 'type' => 'episode', 'attributes' => ['title' => 'After Retry']]];

    $mockClient = MockClient::global([
        MockResponse::make(['error' => 'cf 522'], 522),
        MockResponse::make(['error' => 'origin 500'], 500),
        MockResponse::make([
            'data' => $episodes,
            'meta' => ['currentPage' => 1, 'totalPages' => 1],
        ]),
    ]);

    $result = $this->transistorService->getAll($showId);

    $mockClient->assertSentCount(3, GetEpisodesRequest::class);
    expect($result)->toBe($episodes);
});

it('retries a response with a malformed JSON body and ultimately succeeds', function () {
    $episodes = [['id' => '7', 'type' => 'episode', 'attributes' => ['title' => 'After Retry']]];

    $mockClient = MockClient::global([
        MockResponse::make("{\"data\": [{\"id\": \"7\", \"title\": \"Broken\x01\"}]"),
        MockResponse::make([
            'data' => $episodes,
            'meta' => ['currentPage' => 1, 'totalPages' => 1],
        ]),
    ]);

    $result = $this->transistorService->getRecent('malformed-show');

    $mockClient->assertSentCount(2, GetEpisodesRequest::class);
    expect($result)->toBe($episodes);
});

it('serves stale cache when every response has a malformed JSON body', function () {
    Log::spy();

    $showId = 'malformed-show';
    $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=published';
    $stale = [['id' => 'stale-1']];
    Cache::forever($url, $stale);

    $mockClient = MockClient::global([
        MockResponse::make("{\"data\": [\x01"),
        MockResponse::make("{\"data\": [\x01"),
        MockResponse::make("{\"data\": [\x01"),
    ]);

    expect(fn () => $this->transistorService->refreshEpisodeList($showId))
        ->not->toThrow(\Throwable::class);

    $mockClient->assertSentCount(3, GetEpisodesRequest::class);
    expect(Cache::get($url))->toBe($stale);
    Log::shouldHaveReceived('warning')->once();
});

it('returns an empty list instead of failing when the uncached refresh keeps getting malformed JSON', function () {
    MockClient::global([
        MockResponse::make("{\"data\": [\x01"),
        MockResponse::make("{\"data\": [\x01"),
        MockResponse::make("{\"data\": [\x01"),
    ]);

    expect($this->transistorService->getRecent('malformed-show'))->toBe([]);
});

it('swallows persistent server exceptions on refreshEpisodeList', function () {
    $showId = 'down-show';

    MockClient::global([
        MockResponse::make(['error' => 'cf 522'], 522),
        MockResponse::make(['error' => 'cf 522'], 522),
        MockResponse::make(['error' => 'cf 522'], 522),
    ]);

    expect(fn () => $this->transistorService->refreshEpisodeList($showId))
        ->not->toThrow(\Throwable::class);

    $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=published';
    expect(Cache::has($url))->toBeFalse();
});

it('serves stale cache when refresh fails on getAll', function () {
    $showId = 'stale-show';
    $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=published';
    $stale = [['id' => 'stale-1', 'type' => 'episode', 'attributes' => ['title' => 'Stale']]];

    // No cache yet, but we simulate the API being down for 3 attempts.
    MockClient::global([
        MockResponse::make(['error' => 'cf 522'], 522),
        MockResponse::make(['error' => 'cf 522'], 522),
        MockResponse::make(['error' => 'cf 522'], 522),
    ]);

    // Pre-populate stale cache (e.g. from a previous successful refresh).
    Cache::forever($url, $stale);

    // Force a refresh path by clearing the read-cache check is unnecessary — getAll
    // returns the cached value directly. Instead we test refreshEpisodeList preserves it.
    $this->transistorService->refreshEpisodeList($showId);

    expect(Cache::get($url))->toBe($stale);
});

it('serializes concurrent refreshes using a cache lock', function () {
    $showId = 'locked-show';
    $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=published';
    $episodes = [['id' => '42', 'type' => 'episode', 'attributes' => ['title' => 'Cached While Waiting']]];

    $lock = Cache::lock("transistor-refresh:{$showId}:published", 10);
    $lock->get();

    // Simulate another process populating the cache while the lock is held.
    Cache::forever($url, $episodes);
    $lock->release();

    $mockClient = MockClient::global([
        GetEpisodesRequest::class => MockResponse::make([
            'data' => [],
            'meta' => ['currentPage' => 1, 'totalPages' => 1],
        ]),
    ]);

    $result = $this->transistorService->getAll($showId);

    expect($result)->toBe($episodes);
    $mockClient->assertSentCount(0, GetEpisodesRequest::class);
});

it('keeps the cached episodes when a refresh cannot acquire the lock in time', function () {
    Sleep::fake(syncWithCarbon: true);
    Log::spy();

    $showId = 'contended-show';
    $url = config('services.transistor.api_url') . 'episodes?show_id=' . $showId . '&status=published';
    $cached = [['id' => 'cached-episode']];
    Cache::forever($url, $cached);

    $lock = Cache::lock("transistor-refresh:{$showId}:published", 60);
    $lock->get();

    $mockClient = MockClient::global([
        GetEpisodesRequest::class => MockResponse::make([
            'data' => [],
            'meta' => ['currentPage' => 1, 'totalPages' => 1],
        ]),
    ]);

    $this->transistorService->refreshEpisodeList($showId);

    expect(Cache::get($url))->toBe($cached);
    $mockClient->assertSentCount(0, GetEpisodesRequest::class);
    Log::shouldHaveReceived('warning')->once()->with(
        'Transistor refresh lock timeout; keeping existing cache.',
        ['show_id' => $showId, 'status' => 'published'],
    );

    $lock->release();
});

it('preserves cached episodes and shares the API cooldown after a 429', function (?string $retryAfter, int $cooldown) {
    $url = config('services.transistor.api_url') . 'episodes?show_id=limited-show&status=draft';
    $stale = [['id' => 'cached-episode']];
    Cache::forever($url, $stale);

    $mockClient = MockClient::global([
        MockResponse::make([
            'data' => [['id' => 'partial-refresh']],
            'meta' => ['currentPage' => 1, 'totalPages' => 2],
        ]),
        MockResponse::make([], 429, $retryAfter === null ? [] : ['Retry-After' => $retryAfter]),
        MockResponse::make(['data' => [], 'meta' => ['currentPage' => 1, 'totalPages' => 1]]),
    ]);

    try {
        $this->transistorService->refreshEpisodeList('limited-show', 'draft');
        $this->fail('Expected the API cooldown to interrupt pagination.');
    } catch (RateLimitReachedException $exception) {
        expect($exception->getLimit()->getRemainingSeconds())->toBe($cooldown);
    }

    expect(Cache::get($url))->toBe($stale);
    expect(fn () => $this->transistorService->refreshEpisodeList('another-show', 'published'))
        ->toThrow(RateLimitReachedException::class);
    $mockClient->assertSentCount(2);
})->with([
    'documented cooldown without a header' => [null, 10],
    'longer retry header' => ['45', 45],
    'invalid retry header' => ['invalid', 10],
    'shorter retry header' => ['2', 10],
]);
