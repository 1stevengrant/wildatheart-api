<?php

use App\Actions\Event\ApproveEvent;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
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
