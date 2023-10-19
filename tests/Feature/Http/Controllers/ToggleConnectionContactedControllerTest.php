<?php

use App\Models\Attendee;
use App\Models\Campfire;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->campfire = Campfire::factory()->create();
    $this->attendees = Attendee::factory()->count(3)->create([
        'campfire_id' => $this->campfire->id,
        'user_id' => $this->campfire->host->id,
    ]);
});

test('it marks an attendee as contacted', function () {

    $user = $this->campfire->host;
    $attendee = $this->campfire->attendees->first();
    $this->actingAs($user)
        ->put(route('toggle.contacted', $attendee))
        ->assertSessionHas('notifications.message', 'Connection updated');

    expect($attendee->fresh()->contacted_on)->not->toBeNull();
});

test('it marks an attendee as not contacted', function () {
    $user = $this->campfire->host;
    $attendee = $this->campfire->attendees->first();
    $attendee->update([
        'contacted_on' => now(),
    ]);
    $this->actingAs($user)
        ->put(route('toggle.contacted', $attendee))
        ->assertSessionHas('notifications.message', 'Connection updated');

    expect($attendee->fresh()->contacted_on)->toBeNull();
});

test('it only allows the host to mark an attendee as contacted', function () {
    $user = User::factory()->create();
    $attendee = $this->campfire->attendees->first();
    $this->actingAs($user)
        ->put(route('toggle.contacted', $attendee))
        ->assertStatus(403);
});
