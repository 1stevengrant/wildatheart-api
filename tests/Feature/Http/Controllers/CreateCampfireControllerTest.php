<?php

use App\Mail\Campfire\SendEmailVerificationEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'profile_step' => 2,
    ]);
    Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

});

it('allows a user to create a fire', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ]);

    $this->assertDatabaseHas('campfires', [
        'name' => 'The',
        'country' => 'US',
        'latitude' => '123',
        'longitude' => '123',
    ]);
});

test('a fire needs a name', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => '',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ])
        ->assertSessionHasErrors('name');
});

test('a fire name needs to be at least 2 characters', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'T',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ])
        ->assertSessionHasErrors('name');
});

test('a fire name cannot be more than 255 characters', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => str_repeat('a', 256),
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ])
        ->assertSessionHasErrors('name');
});

test('a fire name cannot end with fire', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The Fire',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ])
        ->assertSessionHasErrors('name');
});

test('a fire name cannot contain emojis', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The Fire 🔥',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ])
        ->assertSessionHasErrors('name');
});

test('a fire needs a country', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => '',
            'latitude' => '123',
            'longitude' => '123',
        ])
        ->assertSessionHasErrors('country');
});

test('a fire needs a latitude', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '',
            'longitude' => '123',
        ])
        ->assertSessionHasErrors('latitude');
});

test('a fire needs a longitude', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '',
        ])
        ->assertSessionHasErrors('longitude');
});

test('a users role is updated to host', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ]);

    $this->user->refresh();

    expect($this->user->hasRole(User::ROLE_HOST))->toBeTrue();
});

test('it sends a verification email to an unverified user', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $this->user->update([
        'email_verified_at' => null,
    ]);

    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ]);

    \Illuminate\Support\Facades\Mail::assertQueued(SendEmailVerificationEmail::class);
});

test('it does not send a verification email to a verified user', function () {
    \Illuminate\Support\Facades\Mail::fake();
    $this->user->update([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ]);

    \Illuminate\Support\Facades\Mail::assertNotQueued(SendEmailVerificationEmail::class);
});

it('approves a campfire', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ]);

    $this->assertDatabaseHas('campfires', [
        'name' => 'The',
        'country' => 'US',
        'latitude' => '123',
        'longitude' => '123',
        'approved_at' => now(),
    ]);
});

it('redirects the user to the gift request page after success', function () {
    $response = $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
        ]);

    $campfire = \App\Models\Campfire::first();
    $response->assertRedirect(route('gift', $campfire));
});
