<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'profile_step' => 2,
    ]);
});

it('shows the host fire form', function () {
    $this->actingAs($this->user)
        ->get(route('host.fire'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('HostFire')
        );
});
