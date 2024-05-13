<?php

use App\Models\Attendee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\UserSeeder::class);
    $this->seed(\Database\Seeders\EmailTemplateSeeder::class);
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = \App\Models\Event::factory()->fires()->create();
    $this->attendees = Attendee::factory()->count(3)->create([
        'event_id' => $this->event->id,
        'user_id' => $this->event->host->id,
    ]);
});

test('it marks an attendee as contacted', function () {
    $this->withoutExceptionHandling();
    $user = $this->event->host;
    $attendee = $this->event->attendees->first();
    $this->actingAs($user)
        ->put(route('toggle.contacted', $attendee))
        ->assertSessionHas('notifications.message', 'Connection updated');

    expect($attendee->fresh()->contacted_on)->not->toBeNull();
});

it('marks an attendee as not contacted', function () {
    $user = $this->event->host;
    $attendee = $this->event->attendees->first();
    $attendee->update([
        'contacted_on' => now(),
    ]);
    $this->actingAs($user)
        ->put(route('toggle.contacted', $attendee))
        ->assertSessionHas('notifications.message', 'Connection updated');

    expect($attendee->fresh()->contacted_on)->toBeNull();
});

test('it only allows the host to mark an attendee as contacted', function () {
    $user = User::factory()->create(['agreed_to_host_agreement_date' => now()]);
    $attendee = $this->event->attendees->first();
    $this->actingAs($user)
        ->put(route('toggle.contacted', $attendee))
        ->assertStatus(403);
});
