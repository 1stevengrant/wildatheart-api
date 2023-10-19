<?php

use Inertia\Testing\AssertableInertia as Assert;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// test that the edit fire page loads and used the Profile/EditFire inertia component
beforeEach(function () {
    $this->campfire = \App\Models\Campfire::factory()->create();
});
it('loads the edit fire page for an authenticated user that owns the fire', function () {
    $this->actingAs($this->campfire->host)
        ->get(route('my.campfires.edit', $this->campfire))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/EditFire')
        );
});

it('redirects to the login page if the user is not logged in', function () {
    $this->get(route('my.campfires.edit', $this->campfire))
        ->assertRedirect(route('login'));
});

it('redirects to the home page if the user is logged in but does not own the fire', function () {
    $this->actingAs(\App\Models\User::factory()->create())
        ->get(route('my.campfires.edit', $this->campfire))
        ->assertRedirect(route('home'));
});
