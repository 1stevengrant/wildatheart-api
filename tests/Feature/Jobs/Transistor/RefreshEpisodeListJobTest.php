<?php

use Saloon\RateLimitPlugin\Limit;
use Illuminate\Queue\Jobs\FakeJob;
use App\Services\TransistorService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\CallQueuedHandler;
use App\Jobs\Transistor\RefreshEpisodeListJob;
use Saloon\RateLimitPlugin\Exceptions\RateLimitReachedException;

beforeEach(function () {
    Cache::flush();
});

afterEach(function () {
    Cache::flush();
});

it('releases a throttled refresh', function () {
    $limit = Limit::allow(5)->everySeconds(10)->name('too_many_attempts_limit');
    $limit->exceeded(45);

    $service = Mockery::mock(TransistorService::class);
    $service->shouldReceive('refreshEpisodeList')->once()
        ->with('wildatheart-daily', 'draft')
        ->andThrow(new RateLimitReachedException($limit));

    $job = (new RefreshEpisodeListJob('wildatheart-daily', 'draft'))->withFakeQueueInteractions();
    $job->handle($service);

    $job->assertReleased()->assertNotFailed();
});

it('still propagates unrelated failures', function () {
    $service = Mockery::mock(TransistorService::class);
    $service->shouldReceive('refreshEpisodeList')->once()->andThrow(new RuntimeException('Unexpected failure'));

    (new RefreshEpisodeListJob('wildatheart-daily', 'draft'))->handle($service);
})->throws(RuntimeException::class, 'Unexpected failure');

it('serializes a fixed retry deadline for throttled jobs', function () {
    $this->freezeTime();
    $job = new RefreshEpisodeListJob('wildatheart-daily', 'draft');
    $queue = app('queue')->connection('sync');
    $payload = json_decode((new ReflectionMethod($queue, 'createPayload'))->invoke($queue, $job, 'default'), true);

    expect($payload['retryUntil'])->toBe(now()->addHour()->getTimestamp())
        ->and($payload['maxTries'])->toBeNull()
        ->and($payload['maxExceptions'])->toBe(3);
});

it('only queues one refresh per show and status', function () {
    Queue::fake();

    RefreshEpisodeListJob::dispatch('wildatheart-daily', 'draft');
    RefreshEpisodeListJob::dispatch('wildatheart-daily', 'draft');
    RefreshEpisodeListJob::dispatch('wildatheart-daily', 'published');
    RefreshEpisodeListJob::dispatch('another-show', 'draft');

    Queue::assertPushed(RefreshEpisodeListJob::class, 3);
    Queue::assertPushed(RefreshEpisodeListJob::class, fn (RefreshEpisodeListJob $job) => $job->showId === 'wildatheart-daily' && $job->status === 'published');
    Queue::assertPushed(RefreshEpisodeListJob::class, fn (RefreshEpisodeListJob $job) => $job->showId === 'another-show' && $job->status === 'draft');
});

it('keeps a refresh unique through throttled retries and unlocks after completion', function () {
    Queue::fake();

    RefreshEpisodeListJob::dispatch('wildatheart-daily', 'draft');
    $job = Queue::pushed(RefreshEpisodeListJob::class)->first();
    $data = ['command' => serialize($job)];
    $limit = Limit::allow(5)->everySeconds(10)->name('too_many_attempts_limit');
    $limit->exceeded(10);

    $service = $this->mock(TransistorService::class);
    $service->shouldReceive('refreshEpisodeList')->once()->ordered()
        ->with('wildatheart-daily', 'draft')
        ->andThrow(new RateLimitReachedException($limit));
    $service->shouldReceive('refreshEpisodeList')->once()->ordered()
        ->with('wildatheart-daily', 'draft');

    $handler = app(CallQueuedHandler::class);
    $throttledAttempt = new FakeJob;
    $handler->call($throttledAttempt, $data);

    expect($throttledAttempt->isReleased())->toBeTrue();

    $this->travel(30)->minutes();
    RefreshEpisodeListJob::dispatch('wildatheart-daily', 'draft');
    Queue::assertPushed(RefreshEpisodeListJob::class, 1);

    $successfulAttempt = new FakeJob;
    $handler->call($successfulAttempt, $data);

    expect($successfulAttempt->isDeleted())->toBeTrue();

    RefreshEpisodeListJob::dispatch('wildatheart-daily', 'draft');
    Queue::assertPushed(RefreshEpisodeListJob::class, 2);
});

it('allows a new refresh after the previous job permanently fails', function () {
    Queue::fake();

    RefreshEpisodeListJob::dispatch('wildatheart-daily', 'draft');
    $job = Queue::pushed(RefreshEpisodeListJob::class)->first();

    app(CallQueuedHandler::class)->failed(
        ['command' => serialize($job)],
        new RuntimeException('Refresh failed'),
        'failed-refresh',
    );

    RefreshEpisodeListJob::dispatch('wildatheart-daily', 'draft');
    Queue::assertPushed(RefreshEpisodeListJob::class, 2);
});
