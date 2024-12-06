<?php

use App\Models\Event;
use App\Mail\Event\SendWelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Database\Seeders\EventTypeSeeder;
use App\Actions\Event\CheckWelcomeStatus;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});
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
