<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->campfire = \App\Models\Campfire::factory()->create();
});
test('loads a list of my connections', function () {
    $this->actingAs($this->campfire->host)
        ->get(route('my.connections'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyConnections')
        );
});
