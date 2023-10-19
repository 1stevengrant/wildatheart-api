<?php

use App\Models\Campfire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
});

test('a user cannot claim a gift for a fire that has already claimed a gift', function () {
    $campfire = Campfire::factory()->create([
        'gift_requested' => true,
    ]);
    $this->actingAs($campfire->host)
        ->get(route('gift', $campfire))
        ->assertStatus(403);
});

test("a gift for a fire that hasn't been claimed, can be requested", function () {
    $campfire = Campfire::factory()->create();
    $this->actingAs($campfire->host)
        ->get(route('gift', $campfire))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('SendGift')
        );
});

test('logged out user cannot request a gift', function () {
    $campfire = Campfire::factory()->create();
    $this->get(route('gift', $campfire))
        ->assertRedirect(route('login'));
});
