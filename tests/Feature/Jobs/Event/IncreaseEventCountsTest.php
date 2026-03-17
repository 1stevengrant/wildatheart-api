<?php

use App\Jobs\Event\IncreaseEventCountsJob;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

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
