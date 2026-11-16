<?php

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

test('a user cannot claim a gift for an event that has already claimed a gift', function () {
    $event = Event::factory()->fires()->create([
        'gift_requested' => true,
    ]);
    $this->actingAs($event->user)
        ->get(route('gift.index', $event))
        ->assertStatus(403);
});

test("a gift for an event that hasn't been claimed, can be requested", function () {
    $event = Event::factory()->fires()->create();
    $this->actingAs($event->user)
        ->get(route('gift.index', $event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('SendGiftPage')
        );
});

test('logged out user cannot request a gift', function () {
    $event = Event::factory()->fires()->create();
    $this->get(route('gift.index', $event))
        ->assertRedirect(route('login'));
});

test('a user who does not own the event cannot open its gift request', function () {
    $event = Event::factory()->fires()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('gift.index', $event))
        ->assertForbidden();
});
