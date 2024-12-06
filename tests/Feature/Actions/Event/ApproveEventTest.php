<?php

use App\Models\Event;
use App\Actions\Event\ApproveEvent;

beforeEach(function () {
    \App\Models\User::factory()->create();
    $this->seed(\Database\Seeders\EmailTemplateSeeder::class);
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
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
