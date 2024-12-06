<?php

use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use App\Mail\Event\NewEventCreatedMailable;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});

test('when an event is created, it sends the new event created email', function () {
    Mail::fake();
    Event::factory()->fires()->create();
    Mail::assertQueued(NewEventCreatedMailable::class);
});
