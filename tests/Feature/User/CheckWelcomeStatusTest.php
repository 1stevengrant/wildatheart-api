<?php

use App\Actions\Event\CheckWelcomeStatus;
use App\Mail\Event\SendWelcomeEmail;
use App\Models\Event;

it('sends the welcome email', function () {
    Mail::fake();

    $event = Event::factory()->create([
        'welcome_sent' => false,
    ]);

    $this->assertFalse($event->welcome_sent);

    CheckWelcomeStatus::run(event: $event);

    $event->refresh();

    $this->assertTrue((bool) $event->welcome_sent);

    Mail::assertQueued(SendWelcomeEmail::class);
});
