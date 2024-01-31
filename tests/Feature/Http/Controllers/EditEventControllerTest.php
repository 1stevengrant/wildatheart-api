<?php

use Inertia\Testing\AssertableInertia as Assert;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = \App\Models\Event::factory()->fires()->create();
});
it('loads the edit event page for an authenticated user that owns the event', function () {
    $this->actingAs($this->event->host)
        ->get(route('my.events.edit', $this->event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/EditEvent')
        );
});

it('redirects to the login page if the user is not logged in', function () {
    $this->get(route('my.events.edit', $this->event))
        ->assertRedirect(route('login'));
});

it('redirects to the home page if the user is logged in but does not own the event', function () {
    $this->actingAs(\App\Models\User::factory()->create())
        ->get(route('my.events.edit', $this->event))
        ->assertRedirect(route('home'));
});
