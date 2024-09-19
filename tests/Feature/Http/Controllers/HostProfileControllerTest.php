<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->newAttributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'phone' => '1234567890',
        'opted_into_marketing' => true,
    ];
});

it('shows the update profile form', function () {

    $this->actingAs($this->user)
        ->get(route('host.index'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Host')
        );
});

it('redirects to the create an event page if the user has already completed the profile', function () {
    $this->user->update(['profile_step' => 2]);

    $this->actingAs($this->user)
        ->get(route('host.index'))
        ->assertRedirect(route('host.eventType.select'));
});

it('redirects to the login page if the user is not logged in', function () {
    $this->get(route('host.index'))
        ->assertRedirect(route('login'));
});

it('allows the user to update their profile', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), $this->newAttributes)
        ->assertSessionHas('notifications.message', 'User Profile Updated!');

    expect($this->user->fresh()->given_name)->toBe('John')
        ->and($this->user->fresh()->family_name)->toBe('Doe')
        ->and($this->user->fresh()->phone)->toBe('1234567890')
        ->and($this->user->fresh()->opted_into_marketing)->toBe(1);

});

test('updating profile requires first name', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['given_name' => '']))
        ->assertInvalid(['given_name']);
});

test('updating profile requires first name to be less than 255 chars', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['given_name' => str_repeat('a', 256)]))
        ->assertInvalid(['given_name']);
});

test('updating profile requires first name to be at min 2 chars', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['given_name' => 'a']))
        ->assertInvalid(['given_name']);
});

test('updating profile requires last name', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['family_name' => '']))
        ->assertInvalid(['family_name']);
});

test('updating profile requires last name to be 255 chars max', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['family_name' => str_repeat('a', 256)]))
        ->assertInvalid(['family_name']);
});

test('updating profile requires last name to be 2 chars min', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['family_name' => 'a']))
        ->assertInvalid(['family_name']);
});

test('updating profile requires phone', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['phone' => '']))
        ->assertInvalid(['phone']);
});

test('updating profile requires phone to be no more than 20 digits', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['phone' => str_repeat('1', 21)]))
        ->assertInvalid(['phone']);
});

test('updating profile requires phone to be min 9 digits', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['phone' => str_repeat('1', 8)]))
        ->assertInvalid(['phone']);
});

test('updating profile requires marketing preferences to be set', function () {
    $this->actingAs($this->user)
        ->put(route('user.profile'), array_merge($this->newAttributes, ['opted_into_marketing' => '']))
        ->assertInvalid(['opted_into_marketing']);
});
