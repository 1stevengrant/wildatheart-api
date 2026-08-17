<?php

use App\Models\Event;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\NewEventCreatedMailable;

test('when an event is created, it sends the new event created email', function () {
    Bus::fake();
    Mail::fake();
    Event::factory()->fires()->create();
    Mail::assertQueued(NewEventCreatedMailable::class);
});

test('it does not break link text mid-word', function () {
    Bus::fake();
    Mail::fake();

    EmailTemplate::where('slug', 'send-fires-welcome-email')->firstOrFail()->update([
        'body' => "Review the [facilitator's guide](https://example.com/becoming-a-king-retreat/event-guide).",
    ]);

    $event = Event::factory()->fires()->create();

    $html = (new NewEventCreatedMailable($event))->render();

    expect($html)->not->toContain('word-break: break-all');
});
