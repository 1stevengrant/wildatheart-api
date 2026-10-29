<?php

use Illuminate\Support\Facades\Queue;
use App\Jobs\Transistor\RefreshDailyReadingsJob;

beforeEach(function () {
    Queue::fake();
});

test('command dispatches refresh daily readings job without rebuild astro by default', function () {
    $this->artisan('daily-readings:refresh')
        ->assertSuccessful()
        ->expectsOutput('Daily readings refresh job dispatched immediately.');

    Queue::assertPushed(RefreshDailyReadingsJob::class, function ($job) {
        return $job->rebuildAstro === false;
    });
});

test('command dispatches job with rebuild astro flag when option provided', function () {
    $this->artisan('daily-readings:refresh --rebuild-astro')
        ->assertSuccessful()
        ->expectsOutput('Daily readings refresh job dispatched immediately.')
        ->expectsOutput('Astro rebuild will be triggered after completion.');

    Queue::assertPushed(RefreshDailyReadingsJob::class, function ($job) {
        return $job->rebuildAstro === true;
    });
});

test('command dispatches job with delay when delay option provided', function () {
    $this->artisan('daily-readings:refresh --delay=60')
        ->assertSuccessful()
        ->expectsOutput('Daily readings refresh job dispatched with 60 second delay.');

    Queue::assertPushed(RefreshDailyReadingsJob::class);
});

test('command dispatches job with both rebuild astro and delay options', function () {
    $this->artisan('daily-readings:refresh --rebuild-astro --delay=120')
        ->assertSuccessful()
        ->expectsOutput('Daily readings refresh job dispatched with 120 second delay.')
        ->expectsOutput('Astro rebuild will be triggered after completion.');

    Queue::assertPushed(RefreshDailyReadingsJob::class, function ($job) {
        return $job->rebuildAstro === true;
    });
});

test('command clamps the delay to the sqs maximum of 900 seconds', function () {
    $this->freezeTime();

    $this->artisan('daily-readings:refresh --delay=3600')
        ->assertSuccessful()
        ->expectsOutput('Delay of 3600 seconds exceeds the queue maximum, using 900 seconds instead.')
        ->expectsOutput('Daily readings refresh job dispatched with 900 second delay.');

    Queue::assertPushed(
        RefreshDailyReadingsJob::class,
        fn (RefreshDailyReadingsJob $job) => now()->diffInSeconds($job->delay) === 900.0
    );
});
