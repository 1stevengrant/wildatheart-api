<?php

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
});

test('a user cannot claim a gift for an event that has already claimed a gift', function () {
    $event = Event::factory()->create([
        'gift_requested' => true,
    ]);
    $this->actingAs($event->host)
        ->get(route('gift', $event))
        ->assertStatus(403);
});

test("a gift for an event that hasn't been claimed, can be requested", function () {
    $event = Event::factory()->create();
    $this->actingAs($event->host)
        ->get(route('gift', $event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('SendGift')
        );
});

test('logged out user cannot request a gift', function () {
    $event = Event::factory()->create();
    $this->get(route('gift', $event))
        ->assertRedirect(route('login'));
});
