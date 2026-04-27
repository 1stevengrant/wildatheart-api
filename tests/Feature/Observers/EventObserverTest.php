<?php

use App\Models\User;
use App\Models\Event;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

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
