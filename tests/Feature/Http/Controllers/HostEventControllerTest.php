<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'john@xxx.com',
        'profile_step' => 2,
    ]);
});

it('shows the host event form', function () {
    $this->actingAs($this->user)
        ->get(route('host.event'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('HostEvent')
        );
});
