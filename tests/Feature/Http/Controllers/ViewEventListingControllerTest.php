<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->event = \App\Models\Event::factory()->approved()->fires()->create();
});

test('anyone can view an event', function () {
    $this->withoutExceptionHandling();
    $this->get(route('view.event', $this->event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('ViewEvent')
        );
});
