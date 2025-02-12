<?php

use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
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

test('when viewing an event as a logged in user, I can view the event', function () {
    // this test was needed as the controller is checking for auth()->user() and not auth()->user()->id
    // when it dispatches a job to log the activity. The activity logger expects a user model and not an integer.
    $user = \App\Models\User::factory()->create();
    $this->actingAs($user);

    $this->get(route('view.event', $this->event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('ViewEventPage')
        );
});
