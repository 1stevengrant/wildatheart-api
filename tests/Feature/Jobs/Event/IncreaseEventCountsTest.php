<?php

use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->event = \App\Models\Event::factory()->fires()->approved()->create();
});
it('queues the job to increase event counts', function () {
    Queue::fake();

    $response = $this->get(route('view.event', $this->event));

    expect($response->status())->toBe(200);

    Queue::assertPushed(\App\Jobs\Event\IncreaseEventCountsJob::class);
});

it('increases the view count for the event', function () {

    $this->get(route('view.event', $this->event));

    expect($this->event->refresh()->view_count)->toBe(1);
});
