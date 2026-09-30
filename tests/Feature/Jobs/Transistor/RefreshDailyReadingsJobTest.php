<?php

use App\Services\TransistorService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use App\Jobs\Cloudflare\TriggerAstroBuildJob;
use App\Jobs\Transistor\RefreshEpisodeListJob;
use App\Jobs\Transistor\RefreshDailyReadingsJob;

beforeEach(function () {
    Cache::flush();

    Bus::fake()->except([RefreshDailyReadingsJob::class]);
});

it('dispatches a separate refresh job for each episode status', function () {
    RefreshDailyReadingsJob::dispatchSync();

    foreach (['published', 'scheduled', 'draft'] as $status) {
        Bus::assertDispatched(
            RefreshEpisodeListJob::class,
            fn (RefreshEpisodeListJob $job) => $job->showId === RefreshDailyReadingsJob::SHOW_ID
                && $job->status === $status
        );
    }

    Bus::assertDispatchedTimes(RefreshEpisodeListJob::class, 3);
});

it('does not trigger an astro build by default', function () {
    RefreshDailyReadingsJob::dispatchSync();

    Bus::assertNotDispatched(TriggerAstroBuildJob::class);
});

it('triggers an astro build when asked to', function () {
    RefreshDailyReadingsJob::dispatchSync(rebuildAstro: true);

    Bus::assertDispatched(TriggerAstroBuildJob::class, fn (TriggerAstroBuildJob $job) => $job->source === TriggerAstroBuildJob::DAILY_READINGS);
});

it('refreshes a single status through the transistor service', function () {
    $service = Mockery::mock(TransistorService::class);
    $service->shouldReceive('refreshEpisodeList')
        ->once()
        ->with('wildatheart-daily', 'published');

    (new RefreshEpisodeListJob('wildatheart-daily', 'published'))->handle($service);
});
