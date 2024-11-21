<?php

use App\Actions\Event\CheckWelcomeStatus;
use App\Mail\Event\SendWelcomeEmail;
use App\Models\Event;
use Database\Seeders\EventTypeSeeder;
use Illuminate\Support\Facades\Mail;

it('sends the welcome email', function () {
    Mail::fake();

    $this->seed(EventTypeSeeder::class);

    $event = Event::factory()->fires()->create([
        'welcome_sent' => false,
    ]);

    $this->assertFalse($event->welcome_sent);

    CheckWelcomeStatus::run(event: $event);

    $event->refresh();

    $this->assertTrue((bool) $event->welcome_sent);

    Mail::assertQueued(SendWelcomeEmail::class);
});
