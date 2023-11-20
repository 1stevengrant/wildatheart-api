<?php

use App\Actions\Event\ApproveEvent;
use App\Models\Event;

it('it approves an event', function () {
    $event = Event::factory()->create([
        'approved_at' => null,
    ]);

    $this->assertNull($event->approved_at);

    ApproveEvent::run(event: $event);

    $event->refresh();

    $this->assertNotNull($event->approved_at);
});
