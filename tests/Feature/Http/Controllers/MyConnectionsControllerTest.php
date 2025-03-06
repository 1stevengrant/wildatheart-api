<?php

use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    $this->event = \App\Models\Event::factory()->fires()->create();
});
test('loads a list of my contacts', function () {
    $this->actingAs($this->event->user)
        ->get(route('my.contacts'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyContactsPage')
        );
});
