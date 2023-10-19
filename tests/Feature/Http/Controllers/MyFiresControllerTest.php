<?php

use App\Models\Campfire;
use Inertia\Testing\AssertableInertia as Assert;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(function () {
    $this->campfire = Campfire::factory()->create();
});

test('it shows a list of fires belonging to the authenticated user', function () {
    $this->actingAs($this->campfire->host)
        ->get(route('my.campfires'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Profile/MyFires')
            ->has('campfires.data', 1)
        );
});
