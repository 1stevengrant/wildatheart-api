<?php

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
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
