<?php

use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Services\Subsplash\SubsplashApiClient;
use Illuminate\Http\Client\ConnectionException;

beforeEach(function () {
    Sleep::fake();
    Cache::flush();

    config()->set('services.subsplash.client_id', 'test-client');
    Cache::put('test-client', 'test-token', 60);

    $this->client = new SubsplashApiClient;
    $this->url = 'https://api.subsplash.test/media/v1/media-items/abc';
});

it('retries after a connection failure', function () {
    Http::fake([
        $this->url => Http::sequence()
            ->pushFailedConnection()
            ->push(['title' => 'Recovered']),
    ]);

    expect($this->client->fetch($this->url))->toBe(['title' => 'Recovered']);

    Http::assertSentCount(2);
});

it('surfaces the connection failure once retries run out', function () {
    Http::fake([$this->url => Http::failedConnection()]);

    expect(fn () => $this->client->fetch($this->url))->toThrow(ConnectionException::class);

    Http::assertSentCount(4);
});

it('retries a server error', function () {
    Http::fake([
        $this->url => Http::sequence()
            ->push('Bad Gateway', 502)
            ->push(['title' => 'Recovered']),
    ]);

    expect($this->client->fetch($this->url))->toBe(['title' => 'Recovered']);

    Http::assertSentCount(2);
});

it('does not retry a missing item', function () {
    Http::fake([$this->url => Http::response([], 404)]);

    expect($this->client->fetch($this->url))->toBeNull();

    Http::assertSentCount(1);
});
