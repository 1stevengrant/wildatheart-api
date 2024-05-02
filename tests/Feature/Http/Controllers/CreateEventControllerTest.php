<?php

use App\Mail\Event\SendEmailVerificationEmail;
use App\Models\EventType;
use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\UserSeeder::class);
    $this->seed(\Database\Seeders\EventTypeSeeder::class);
    $this->seed(\Database\Seeders\EmailTemplateSeeder::class);
    $this->user = User::factory()->create([
        'profile_step' => 2,
    ]);
    Artisan::call('db:seed', ['--class' => 'RoleSeeder']);

});

it('allows a user to create a event', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
            'event_type_id' => 1,
        ]);

    $this->assertDatabaseHas('events', [
        'name' => 'The',
        'country' => 'US',
        'latitude' => '123',
        'longitude' => '123',
    ]);
});

test('an event needs a name', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => '',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
            'event_type_id' => 1,
        ])
        ->assertSessionHasErrors('name');
});

test('an event name needs to be at least 2 characters', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'T',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
            'event_type_id' => 1,
        ])
        ->assertSessionHasErrors('name');
});

test('an event name cannot be more than 255 characters', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => str_repeat('a', 256),
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
            'event_type_id' => 1,
        ])
        ->assertSessionHasErrors('name');
});

test('an event name cannot end with fire', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The Fire',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
            'event_type_id' => 1,
        ])
        ->assertSessionHasErrors('name');
});

test('an event name cannot contain emojis', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The Fire 🔥',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
            'event_type_id' => 1,
        ])
        ->assertSessionHasErrors('name');
});

test('an event needs a country', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => '',
            'latitude' => '123',
            'longitude' => '123',
            'event_type_id' => 1,
        ])
        ->assertSessionHasErrors('country');
});

test('an event needs a latitude', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '',
            'longitude' => '123',
            'event_type_id' => 1,
        ])
        ->assertSessionHasErrors('latitude');
});

test('an event needs a longitude', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '',
            'event_type_id' => 1,
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
            'event_type_id' => EventType::where('name', EventType::FIRES)->first()->id,
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
            'event_type_id' => EventType::where('name', EventType::FIRES)->first()->id,
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
            'event_type_id' => 1,
        ]);

    \Illuminate\Support\Facades\Mail::assertNotQueued(SendEmailVerificationEmail::class);
});

it('approves an event', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
            'event_type_id' => EventType::where('name', EventType::FIRES)->first()->id,
        ]);

    $this->assertDatabaseHas('events', [
        'name' => 'The',
        'country' => 'US',
        'latitude' => '123',
        'longitude' => '123',
        'approved_at' => now(),
    ]);
});

test('an event has an event type', function () {
    $this->actingAs($this->user)
        ->post(route('host.store'), [
            'name' => 'The',
            'country' => 'United States',
            'latitude' => '123',
            'longitude' => '123',
            'event_type_id' => EventType::where('name', EventType::FIRES)->first()->id,
        ]);

    $this->assertDatabaseHas('events', [
        'name' => 'The',
        'event_type_id' => EventType::where('name', EventType::FIRES)->first()->id,
    ]);
});
