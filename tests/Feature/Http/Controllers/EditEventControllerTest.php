<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Bus::fake();
    Mail::fake();

    $this->event = \App\Models\Event::factory()->fires()->create();
});
it('loads the edit event page for an authenticated user that owns the event', function () {
    $this->actingAs($this->event->user)
        ->get(route('my.events.edit', $this->event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/EditEventPage')
        );
});

it('redirects to the login page if the user is not logged in', function () {
    $this->get(route('my.events.edit', $this->event))
        ->assertRedirect(route('login'));
});

it('returns 403 if the user is logged in but does not own the event', function () {
    // Use non-wildatheart.org email to avoid automatic admin role assignment
    $this->actingAs(\App\Models\User::factory()->create(['email' => 'other@example.com']))
        ->get(route('my.events.edit', $this->event))
        ->assertForbidden();
});
