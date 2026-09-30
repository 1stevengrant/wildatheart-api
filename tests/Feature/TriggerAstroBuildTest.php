<?php

use Statamic\Facades\Entry;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Actions\Astro\TriggerAstroBuild;
use App\Jobs\Cloudflare\TriggerAstroBuildJob;

it('does not call the deploy hook while the app is in the testing environment', function () {
    Http::fake();

    TriggerAstroBuild::run();

    Http::assertNothingSent();
});

it('posts to the Cloudflare Astro build hook', function () {
    Http::fake([
        'https://api.cloudflare.com/client/v4/workers/builds/deploy_hooks/test-hook' => Http::response([
            'success' => true,
        ], 200),
    ]);

    $this->app['env'] = 'staging';
    config()->set('cloudflare.astro_build_hook', 'https://api.cloudflare.com/client/v4/workers/builds/deploy_hooks/test-hook');

    TriggerAstroBuild::run();

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.cloudflare.com/client/v4/workers/builds/deploy_hooks/test-hook'
            && $request->method() === 'POST';
    });
});

it('throws when the deploy hook is missing', function () {
    $this->app['env'] = 'staging';
    config()->set('cloudflare.astro_build_hook', null);

    TriggerAstroBuild::run();
})->throws(InvalidArgumentException::class);

it('throws when the deploy hook rejects the request', function () {
    Http::fake([
        'https://api.cloudflare.com/client/v4/workers/builds/deploy_hooks/test-hook' => Http::response(['success' => false], 500),
    ]);

    $this->app['env'] = 'staging';
    config()->set('cloudflare.astro_build_hook', 'https://api.cloudflare.com/client/v4/workers/builds/deploy_hooks/test-hook');

    TriggerAstroBuild::run();
})->throws(RuntimeException::class);

it('delegates queued rebuilds to the TriggerAstroBuild action', function () {
    $this->mock(TriggerAstroBuild::class, function ($mock) {
        $mock->shouldReceive('handle')->once();
    });

    (new TriggerAstroBuildJob)->handle(app(TriggerAstroBuild::class));
});

it('collapses repeated daily readings dispatches into a single queued build', function () {
    Queue::fake();

    TriggerAstroBuildJob::dispatch(TriggerAstroBuildJob::DAILY_READINGS)->delay(now()->addHour());
    TriggerAstroBuildJob::dispatch(TriggerAstroBuildJob::DAILY_READINGS)->delay(now()->addHour());
    TriggerAstroBuildJob::dispatch(TriggerAstroBuildJob::DAILY_READINGS)->delay(now()->addHour());

    Queue::assertPushed(TriggerAstroBuildJob::class, 1);
});

it('collapses repeated content dispatches while one is still queued', function () {
    Queue::fake();

    TriggerAstroBuildJob::dispatch(TriggerAstroBuildJob::CONTENT);
    TriggerAstroBuildJob::dispatch(TriggerAstroBuildJob::CONTENT);

    Queue::assertPushed(TriggerAstroBuildJob::class, 1);
});

it('still queues a content build while a daily readings build is pending', function () {
    Queue::fake();

    TriggerAstroBuildJob::dispatch(TriggerAstroBuildJob::DAILY_READINGS)->delay(now()->addHour());
    TriggerAstroBuildJob::dispatch(TriggerAstroBuildJob::CONTENT);

    Queue::assertPushed(TriggerAstroBuildJob::class, 2);
    Queue::assertPushed(TriggerAstroBuildJob::class, fn (TriggerAstroBuildJob $job) => $job->source === TriggerAstroBuildJob::CONTENT);
});

it('still queues a daily readings build while a content build is pending', function () {
    Queue::fake();

    TriggerAstroBuildJob::dispatch(TriggerAstroBuildJob::CONTENT);
    TriggerAstroBuildJob::dispatch(TriggerAstroBuildJob::DAILY_READINGS)->delay(now()->addHour());

    Queue::assertPushed(TriggerAstroBuildJob::class, 2);
    Queue::assertPushed(TriggerAstroBuildJob::class, fn (TriggerAstroBuildJob $job) => $job->source === TriggerAstroBuildJob::DAILY_READINGS);
});

it('treats a build queued before sources existed as a content build', function () {
    expect((new TriggerAstroBuildJob)->uniqueId())->toBe(TriggerAstroBuildJob::CONTENT);
});

it('queues a content build when a prayer is saved', function () {
    Bus::fake();
    Mail::fake();

    Entry::make()
        ->collection('prayers')
        ->slug('astro-build-prayer')
        ->published(true)
        ->data(['title' => 'Astro build prayer'])
        ->save();

    Bus::assertDispatched(TriggerAstroBuildJob::class, fn (TriggerAstroBuildJob $job) => $job->source === TriggerAstroBuildJob::CONTENT);
});
