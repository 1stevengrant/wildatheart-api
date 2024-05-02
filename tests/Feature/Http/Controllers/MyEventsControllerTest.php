<?php

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = Event::factory()->fires()->create();
});

test('it shows a list of events belonging to the authenticated user', function () {
    $this->actingAs($this->event->host)
        ->get(route('my.events'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyEvents')
            ->has('events.data', 1)
        );
});
