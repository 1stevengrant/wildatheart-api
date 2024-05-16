<?php

use App\Models\Event;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
});

test('a user cannot claim a gift for an event that has already claimed a gift', function () {
    $event = Event::factory()->fires()->create([
        'gift_requested' => true,
    ]);
    $this->actingAs($event->host)
        ->get(route('gift', $event))
        ->assertStatus(403);
})->skip('TODO: query with Jamie if still needed');

test("a gift for an event that hasn't been claimed, can be requested", function () {
    $event = Event::factory()->fires()->create();
    $this->actingAs($event->host)
        ->get(route('gift', $event))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('SendGiftPage')
        );
})->skip('TODO: query with Jamie if still needed');

test('logged out user cannot request a gift', function () {
    $event = Event::factory()->fires()->create();
    $this->get(route('gift', $event))
        ->assertRedirect(route('login'));
})->skip('TODO: query with Jamie if still needed');
