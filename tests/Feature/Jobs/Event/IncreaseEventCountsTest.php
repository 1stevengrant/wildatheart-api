<?php

use App\Models\Event;
use App\Models\Activity;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Jobs\Event\IncreaseEventCountsJob;

beforeEach(function () {
    Bus::fake()->except(IncreaseEventCountsJob::class);
    Mail::fake();

    $this->event = Event::factory()->fires()->approved()->create();
});

it('queues the job to increase event counts', function () {
    Queue::fake();

    $response = $this->get(route('view.event', $this->event));

    expect($response->status())->toBe(200);

    Queue::assertPushed(IncreaseEventCountsJob::class);
});

it('increases the view count for the event', function () {
    $this->get(route('view.event', $this->event));

    expect($this->event->refresh()->view_count)->toBe(1);
});

it('does not count twice when the activity log write fails and the job is retried', function () {
    $job = new IncreaseEventCountsJob($this->event, Event::COUNT_TYPE_ATTENDEE);
    $shouldFailActivityLogWrite = true;

    Activity::creating(function () use (&$shouldFailActivityLogWrite): void {
        if ($shouldFailActivityLogWrite) {
            throw new RuntimeException('Activity log write failed');
        }
    });

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'Activity log write failed')
        ->and($this->event->refresh()->attendee_count)->toBe(0);

    $shouldFailActivityLogWrite = false;

    $job->handle();

    expect($this->event->refresh()->attendee_count)->toBe(1);
});
