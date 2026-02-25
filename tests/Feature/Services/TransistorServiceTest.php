<?php

namespace Tests\Feature\Services;

use Saloon\Http\Faking\MockClient;
use App\Services\TransistorService;
use Illuminate\Support\Facades\Http;
use Saloon\Http\Faking\MockResponse;
use Illuminate\Support\Facades\Cache;
use App\Http\Integrations\Transistor\Requests\GetEpisodesRequest;

beforeEach(function () {
    Cache::flush();

    config([
        'services.transistor.api_key' => 'test-api-key',
        'services.transistor.api_url' => 'https://api.transistor.fm/v1/',
        'services.transistor.cache_duration' => 3600,
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
