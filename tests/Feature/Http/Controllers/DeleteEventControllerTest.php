<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->event = Event::factory()->fires()->create();
});

test('it allows a user to soft delete their own event', function () {
    $this->actingAs($this->event->user)
        ->delete(route('delete.event', $this->event));

    expect($this->event->fresh()->deleted_at)->not->toBeNull();
});

test('a user can only delete their own events', function () {
    $unauthorizedUser = User::factory()->create();

    $this->actingAs($unauthorizedUser)
        ->delete(route('delete.event', $this->event))
        ->assertStatus(302)
        ->assertRedirect(route('home'))
        ->assertSessionHas('notifications.message', 'You are not authorized to delete this event.');
});
