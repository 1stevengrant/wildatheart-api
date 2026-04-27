<?php

use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
    $this->event = Event::factory()->fires()->approved()->create();
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
