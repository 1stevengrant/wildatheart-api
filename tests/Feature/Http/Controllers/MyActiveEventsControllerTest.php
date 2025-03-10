<?php

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
    $this->event = Event::factory()->fires()->create();
});

test('it shows a list of events belonging to the authenticated user', function () {
    $this->actingAs($this->event->user)
        ->get(route('my.events.active'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyActiveEventsPage')
            ->has('events.data', 1)
        );
});
