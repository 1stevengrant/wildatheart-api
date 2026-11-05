<?php

use Illuminate\Support\Sleep;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Exceptions;
use App\Services\Subsplash\SubsplashApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Defer\DeferredCallbackCollection;

beforeEach(function () {
    Sleep::fake();
    Cache::flush();

    config()->set([
        'services.subsplash.base_url' => 'https://api.subsplash.test',
        'services.subsplash.client_id' => 'test-client',
        'services.subsplash.client_secret' => 'test-secret',
        'services.subsplash.scope' => 'test-scope',
        'services.subsplash.authentication_token_url' => 'https://api.subsplash.test/token',
        'services.subsplash.cache_duration' => 300,
    ]);
    Cache::put('test-client', 'test-token', 3600);

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

    Http::assertSentCount(3);
    Sleep::assertSequence([Sleep::for(100)->milliseconds(), Sleep::for(500)->milliseconds()]);
});

it('retries a server error', function (int $status) {
    Http::fake([
        $this->url => Http::sequence()
            ->push('Unavailable', $status)
            ->push(['title' => 'Recovered']),
    ]);

    expect($this->client->fetch($this->url))->toBe(['title' => 'Recovered']);

    Http::assertSentCount(2);
})->with([502, 503]);

it('does not retry a missing item', function () {
    Http::fake([$this->url => Http::response([], 404)]);

    expect($this->client->fetch($this->url))->toBeNull();

    Http::assertSentCount(1);
});

it('reports the final 503 after three attempts without caching the failure', function () {
    Http::fake([
        $this->url => Http::sequence()->push([], 503)->push([], 503)->push([], 503)->push(['id' => 'abc']),
    ]);

    expect(fn () => $this->client->fetch($this->url))
        ->toThrow(Exception::class, "Request to Subsplash API failed with 503: {$this->url}");

    Http::assertSentCount(3);
    Sleep::assertSequence([Sleep::for(100)->milliseconds(), Sleep::for(500)->milliseconds()]);
    expect($this->client->fetch($this->url))->toBe(['id' => 'abc']);
    Http::assertSentCount(4);
});

it('serves the stale response and refreshes it after the configured cache expires', function () {
    $this->freezeTime();
    Http::fake([
        $this->url => Http::sequence()->push(['title' => 'Original'])->push(['title' => 'Updated']),
    ]);

    expect($this->client->fetch($this->url))->toBe(['title' => 'Original']);
    $this->travel(299)->seconds();
    expect($this->client->fetch($this->url))->toBe(['title' => 'Original']);
    expect(app(DeferredCallbackCollection::class))->toHaveCount(0);
    Http::assertSentCount(1);

    $this->travel(2)->seconds();
    expect($this->client->fetch($this->url))->toBe(['title' => 'Original']);
    Http::assertSentCount(1);

    app(DeferredCallbackCollection::class)->invoke();

    Http::assertSentCount(2);
    expect($this->client->fetch($this->url))->toBe(['title' => 'Updated']);
    Http::assertSentCount(2);
});

it('keeps serving the stale response and reports the failure when a refresh cannot connect', function () {
    Exceptions::fake();
    $this->freezeTime();
    Http::fake([
        $this->url => Http::sequence()
            ->push(['title' => 'Original'])
            ->pushFailedConnection()
            ->pushFailedConnection()
            ->pushFailedConnection(),
    ]);

    $this->client->fetch($this->url);
    $this->travel(301)->seconds();

    expect($this->client->fetch($this->url))->toBe(['title' => 'Original']);

    app(DeferredCallbackCollection::class)->invoke();

    Exceptions::assertReported(ConnectionException::class);
    Http::assertSentCount(4);
    expect($this->client->fetch($this->url))->toBe(['title' => 'Original']);
});

it('releases the refresh lock after a failed refresh so the next request can retry', function () {
    Exceptions::fake();
    $this->freezeTime();
    Http::fake([
        $this->url => Http::sequence()
            ->push(['title' => 'Original'])
            ->pushFailedConnection()
            ->pushFailedConnection()
            ->pushFailedConnection()
            ->push(['title' => 'Recovered']),
    ]);

    $this->client->fetch($this->url);
    $this->travel(301)->seconds();

    $this->client->fetch($this->url);
    app(DeferredCallbackCollection::class)->invoke();

    $this->client->fetch($this->url);
    app(DeferredCallbackCollection::class)->invoke();

    expect($this->client->fetch($this->url))->toBe(['title' => 'Recovered']);
});

it('surfaces the connection failure once the stale response has expired', function () {
    $this->freezeTime();
    Http::fake([
        $this->url => Http::sequence()
            ->push(['title' => 'Original'])
            ->pushFailedConnection()
            ->pushFailedConnection()
            ->pushFailedConnection(),
    ]);

    $this->client->fetch($this->url);
    $this->travel(86401)->seconds();
    Cache::put('test-client', 'test-token', 3600);

    expect(fn () => $this->client->fetch($this->url))->toThrow(ConnectionException::class);
});

it('stops serving the stale response once Subsplash reports the item as missing', function () {
    $this->freezeTime();
    Http::fake([
        $this->url => Http::sequence()->push(['title' => 'Original'])->push([], 404)->push([], 404),
    ]);

    $this->client->fetch($this->url);
    $this->travel(301)->seconds();

    expect($this->client->fetch($this->url))->toBe(['title' => 'Original']);

    app(DeferredCallbackCollection::class)->invoke();

    expect($this->client->fetch($this->url))->toBeNull();
    Http::assertSentCount(3);
});

it('reads past the cache without changing it when fetching fresh', function () {
    Http::fake([
        $this->url => Http::sequence()->push(['title' => 'Original'])->push(['title' => 'Updated']),
    ]);

    $this->client->fetch($this->url);

    expect($this->client->fetchFresh($this->url))->toBe(['title' => 'Updated']);
    expect($this->client->fetch($this->url))->toBe(['title' => 'Original']);
    Http::assertSentCount(2);
});

it('honours an explicitly disabled response cache', function () {
    config()->set('services.subsplash.cache_duration', 0);
    Http::fake([
        $this->url => Http::sequence()->push(['title' => 'Original'])->push(['title' => 'Updated']),
    ]);

    expect($this->client->fetch($this->url))->toBe(['title' => 'Original']);
    expect($this->client->fetch($this->url))->toBe(['title' => 'Updated']);
    Http::assertSentCount(2);
});

it('refreshes an expired token once and preserves request timeouts', function (int $timeout) {
    $calls = [];
    $attempt = 0;
    Http::fake(function ($request, array $options) use (&$calls, &$attempt) {
        $calls[] = [$request->url(), $options['connect_timeout'], $options['timeout']];
        if ($request->url() === 'https://api.subsplash.test/token') {
            return Http::response(['access_token' => 'refreshed-token', 'expires_in' => 3600]);
        }
        expect($request->url())->toBe($this->url);
        $attempt++;
        if ($attempt === 1) {
            return Http::response([], 401);
        }
        expect($request->header('Authorization'))->toBe(['Bearer refreshed-token']);

        return Http::response(['id' => 'abc']);
    });

    expect($this->client->fetch($this->url, $timeout))->toBe(['id' => 'abc']);

    expect($calls)->toBe([
        [$this->url, 2, $timeout],
        ['https://api.subsplash.test/token', 2, 5],
        [$this->url, 2, $timeout],
    ]);
    Http::assertSentCount(3);
})->with([5, 30]);

it('does not refresh repeatedly when the replacement token is rejected', function () {
    Http::fake([
        'https://api.subsplash.test/token' => Http::response(['access_token' => 'refreshed-token', 'expires_in' => 3600]),
        $this->url => Http::response([], 401),
    ]);

    expect(fn () => $this->client->fetch($this->url))
        ->toThrow(Exception::class, "Request to Subsplash API failed with 401: {$this->url}");

    Http::assertSentCount(3);
    Sleep::assertNeverSlept();
});

it('uses a five second timeout by default', function () {
    Http::fake(function ($request, array $options) {
        expect($request->url())->toBe($this->url);
        expect($options['timeout'])->toBe(5);
        expect($options['connect_timeout'])->toBe(2);

        return Http::response(['id' => 'abc']);
    });

    expect($this->client->fetch($this->url))->toBe(['id' => 'abc']);
    Http::assertSentCount(1);
});
