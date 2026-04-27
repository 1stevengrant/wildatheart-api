<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    $this->event = Event::factory()->fires()->create();
});

test('loads a list of my contacts', function () {
    $this->actingAs($this->event->user)
        ->get(route('my.contacts'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyContactsPage')
        );
});

test('can delete an attendee from own event', function () {
    $attendee = Attendee::factory()->create([
        'event_id' => $this->event->id,
    ]);

    $this->actingAs($this->event->user)
        ->delete(route('my.contacts.destroy', $attendee->id))
        ->assertRedirect(route('my.contacts'))
        ->assertSessionHas('notifications.message', 'Contact removed successfully.')
        ->assertSessionHas('notifications.type', 'success');

    $this->assertSoftDeleted('attendees', [
        'id' => $attendee->id,
    ]);
});

test('cannot delete an attendee from another users event', function () {
    $otherUser = User::factory()->create();
    $otherEvent = Event::factory()->fires()->create([
        'user_id' => $otherUser->id,
    ]);

    $attendee = Attendee::factory()->create([
        'event_id' => $otherEvent->id,
    ]);

    $this->actingAs($this->event->user)
        ->delete(route('my.contacts.destroy', $attendee->id))
        ->assertNotFound();
});

test('cannot delete non-existent attendee', function () {
    $nonExistentId = 99999;

    $this->actingAs($this->event->user)
        ->delete(route('my.contacts.destroy', $nonExistentId))
        ->assertNotFound();
});
