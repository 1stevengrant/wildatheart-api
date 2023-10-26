<?php

use App\Models\Campfire;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->campfire = Campfire::factory()->approved()->create();
});

test('anyone can view a fire', function () {
    $this->get(route('view.fire', $this->campfire))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('ViewFire')
        );
});
