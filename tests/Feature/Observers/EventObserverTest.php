<?php

use App\Models\User;
use App\Models\Event;
use App\Jobs\LogActivityJob;
use App\Jobs\WeGive\TagDonorJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Jobs\Event\SendNewEventSlackAlert;
use App\Mail\Event\NewEventCreatedMailable;
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

it('holds the Slack alert, welcome email and WeGive tagging while an event is a draft', function () {
    Event::factory()->fires()->create(['approved_at' => null, 'is_draft' => true]);

    Bus::assertNotDispatched(SendNewEventSlackAlert::class);
    Bus::assertNotDispatched(TagDonorJob::class);
    Mail::assertNotQueued(NewEventCreatedMailable::class);
});

it('announces a draft once when it is first published', function () {
    $event = Event::factory()->fires()->create(['approved_at' => null, 'is_draft' => true]);

    $event->update(['approved_at' => now()]);

    expect($event->fresh()->is_draft)->toBeFalse();
    Bus::assertDispatchedTimes(SendNewEventSlackAlert::class, 1);
    Bus::assertDispatched(TagDonorJob::class);
    Mail::assertQueued(NewEventCreatedMailable::class, 1);

    $event->update(['approved_at' => null]);
    $event->update(['approved_at' => now()]);

    Bus::assertDispatchedTimes(SendNewEventSlackAlert::class, 1);
    Mail::assertQueued(NewEventCreatedMailable::class, 1);
});

it('keeps announcing non-draft events when they are created, and not again when republished', function () {
    $event = Event::factory()->fires()->create(['approved_at' => null]);

    Bus::assertDispatchedTimes(SendNewEventSlackAlert::class, 1);
    Mail::assertQueued(NewEventCreatedMailable::class, 1);

    $event->update(['approved_at' => now()]);

    Bus::assertDispatchedTimes(SendNewEventSlackAlert::class, 1);
    Mail::assertQueued(NewEventCreatedMailable::class, 1);
});
