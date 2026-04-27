<?php

use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\NewEventCreatedMailable;

test('when an event is created, it sends the new event created email', function () {
    Bus::fake();
    Mail::fake();
    Event::factory()->fires()->create();
    Mail::assertQueued(NewEventCreatedMailable::class);
});
