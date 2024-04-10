<?php

use Database\Seeders\EventTypeSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(EventTypeSeeder::class);
    $this->event = \App\Models\Event::factory()->approved()->fires()->create();
});

test('anyone can view an event', function () {
    $this->withoutExceptionHandling();
    $this->get(route('view.event', $this->event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('ViewEventPage')
        );
});
