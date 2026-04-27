<?php

use App\Models\User;
use App\Models\Attendee;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->seed(\Database\Seeders\UserSeeder::class);
    $this->event = \App\Models\Event::factory()->fires()->create();
    $this->attendees = Attendee::factory()->count(3)->create([
        'event_id' => $this->event->id,
        'user_id' => $this->event->user->id,
    ]);
});

test('it marks an attendee as contacted', function () {

    $user = $this->event->user;
    $attendee = $this->event->attendees->first();
    $this->actingAs($user)
        ->put(route('toggle.contacted', $attendee))
        ->assertSessionHas('notifications.message', 'Contact updated');

    expect($attendee->fresh()->contacted_on)->not->toBeNull();
});

it('marks an attendee as not contacted', function () {
    $user = $this->event->user;
    $attendee = $this->event->attendees->first();
    $attendee->update([
        'contacted_on' => now(),
    ]);
    $this->actingAs($user)
        ->put(route('toggle.contacted', $attendee))
        ->assertSessionHas('notifications.message', 'Contact updated');

    expect($attendee->fresh()->contacted_on)->toBeNull();
});

test('it only allows the host to mark an attendee as contacted', function () {
    $user = User::factory()->create();
    $attendee = $this->event->attendees->first();
    $this->actingAs($user)
        ->put(route('toggle.contacted', $attendee))
        ->assertStatus(403);
});
