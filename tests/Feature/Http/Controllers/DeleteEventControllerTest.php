<?php

use App\Models\Event;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = Event::factory()->fires()->create();
});

test('it allows a user to soft delete their own event', function () {
    $this->actingAs($this->event->host)
        ->delete(route('delete.event', $this->event));

    $this->assertSoftDeleted('events', [
        'id' => $this->event->id,
    ]);
});

test('a user can only delete their own events', function () {
    $unauthorizedUser = \App\Models\User::factory()->create([
        'agreed_to_host_agreement_date' => now(),
    ]);

    $this->actingAs($unauthorizedUser)
        ->delete(route('delete.event', $this->event))
        ->assertStatus(302)
        ->assertRedirect(route('home'))
        ->assertSessionHas('notifications.message', 'You are not authorized to delete this event.');
});
