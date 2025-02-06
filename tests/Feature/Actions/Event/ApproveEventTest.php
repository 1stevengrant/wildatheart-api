<?php

use App\Models\Event;
use App\Actions\Event\ApproveEvent;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    \App\Models\User::factory()->create();
});
it('it approves an event', function () {
    $event = Event::factory()->fires()->create([
        'approved_at' => null,
    ]);

    $this->assertNull($event->approved_at);

    ApproveEvent::run(event: $event);

    $event->refresh();

    $this->assertNotNull($event->approved_at);
});
