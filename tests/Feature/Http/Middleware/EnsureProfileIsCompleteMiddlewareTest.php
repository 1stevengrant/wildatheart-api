<?php

use App\Models\User;
use App\Models\EventType;
use Illuminate\Support\Facades\Bus;

beforeEach(function () {
    Bus::fake();

    $this->eventType = EventType::first();
});

it('shares a profile nudge flag when given_name is missing', function () {
    $user = User::factory()->create(['given_name' => null]);

    $this->actingAs($user)
        ->get(route('host.eventType.select'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('show_profile_nudge', true));
});

it('shares a profile nudge flag when family_name is missing', function () {
    $user = User::factory()->create(['family_name' => null]);

    $this->actingAs($user)
        ->get(route('host.eventType.select'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('show_profile_nudge', true));
});

it('shares a profile nudge flag when phone is missing', function () {
    $user = User::factory()->create(['phone' => null]);

    $this->actingAs($user)
        ->get(route('host.eventType.select'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('show_profile_nudge', true));
});

it('does not share a profile nudge flag when profile is complete', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('host.eventType.select'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->missing('show_profile_nudge'));
});

it('allows access to profile page without complete profile', function () {
    $user = User::factory()->create(['phone' => null]);

    $this->actingAs($user)
        ->get(route('my.profile'))
        ->assertOk();
});

it('allows access to profile update without complete profile', function () {
    $user = User::factory()->create(['phone' => null]);

    $this->actingAs($user)
        ->put(route('user.profile'), [
            'given_name' => 'Jane',
            'family_name' => 'Doe',
            'phone' => '+1234567890',
        ])
        ->assertRedirect();
});
