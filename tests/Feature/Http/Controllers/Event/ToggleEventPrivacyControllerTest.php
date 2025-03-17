<?php

use App\Models\User;
use App\Models\Event;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    $this->user = User::factory()->create();
});

it('toggles the privacy of an event', function () {

    $event = Event::factory()->fires()->approved()->create([
        'user_id' => $this->user->id,
        'is_private' => false,
    ]);

    $this->actingAs($this->user)
        ->put(route('toggle-event-privacy', $event->id));

    $this->assertTrue($event->fresh()->is_private);
});

it('only allows the user who created the event to toggle the privacy', function () {

    $event = Event::factory()->fires()->approved()->create([
        'user_id' => $this->user->id,
        'is_private' => false,
    ]);

    $anotherUser = User::factory()->create();

    $this->actingAs($anotherUser)
        ->put(route('toggle-event-privacy', $event->id))
        ->assertStatus(403);

    $this->assertFalse($event->fresh()->is_private);
});
