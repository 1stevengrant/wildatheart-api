<?php

use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = \App\Models\Event::factory()->fires()->create();
});
test('loads a list of my connections', function () {
    $this->actingAs($this->event->host)
        ->get(route('my.connections'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyConnectionsPage')
        );
});
