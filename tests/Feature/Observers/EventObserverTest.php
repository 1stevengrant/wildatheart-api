<?php

use App\Models\User;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Http\Integrations\WeGive\WeGiveConnector;

beforeEach(function () {
    Mail::fake();
    Bus::fake();
});

it('dispatches WeGive tagging jobs when event is created', function () {
    $host = User::factory()->create(['email' => 'host@example.com']);

    $event = Event::factory()->fires()->approved()->for($host)->create();
    $event->load('eventType');

    Bus::assertDispatched(TagDonorJob::class, function ($job) use ($host, $event) {
        return $job->taggable->id === $host->id
            && $job->weGiveTagId == $event->eventType->wegive_tag;
    });
});

it('uses event type wegive tag when tagging event creator', function () {
    $host = User::factory()->create();

    $event = Event::factory()->fires()->approved()->for($host)->create();
    $event->load('eventType');

    Bus::assertDispatched(TagDonorJob::class, function ($job) use ($event) {
        return $job->weGiveTagId == $event->eventType->wegive_tag;
    });
});

it('tags the correct user when event is created', function () {
    $host = User::factory()->create();

    $event = Event::factory()->fires()->approved()->for($host)->create();

    Bus::assertDispatched(TagDonorJob::class, function ($job) use ($host) {
        return $job->taggable->id === $host->id;
    });
});

it('dispatches WeGive tagging for different event types', function () {
    $host = User::factory()->create();

    $event = Event::factory()->wahExperiences()->approved()->for($host)->create();
    $event->load('eventType');

    Bus::assertDispatched(TagDonorJob::class, function ($job) use ($host, $event) {
        return $job->taggable->id === $host->id
            && $job->weGiveTagId == $event->eventType->wegive_tag; // Use == for type-safe comparison
    });
});

it('does not crash and logs when an event is created without an event type', function () {
    $host = User::factory()->create();

    $event = Event::factory()->for($host)->create(['event_type_id' => null]);

    Bus::assertDispatched(LogActivityJob::class, function ($job) use ($event) {
        return $job->logName === WeGiveConnector::ERROR_LOG_NAME
            && $job->performedOn->is($event);
    });

    Bus::assertNotDispatched(TagDonorJob::class);
});
