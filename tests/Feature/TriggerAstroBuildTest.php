<?php

use Illuminate\Support\Facades\Http;
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
