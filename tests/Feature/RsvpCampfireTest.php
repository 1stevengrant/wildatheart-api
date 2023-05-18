<?php

use App\Http\Livewire\RsvpCampfire;
use App\Models\Campfire;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

test('a known user can rsvp to a given 🔥', function () {
    $campfire = Campfire::factory()->inTheFuture()->approved()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    livewire(RsvpCampfire::class, ['campfire' => $campfire])
        ->call('handle');

    $this->assertDatabaseHas('attendees', [
        'user_id' => auth()->id(),
        'campfire_id' => $campfire->id,
    ]);
});

test('an anonymous user can rsvp to a given 🔥', function () {
    $campfire = Campfire::factory()->inTheFuture()->approved()->create();

    livewire(RsvpCampfire::class, ['campfire' => $campfire])
        ->set('firstName', 'John')
        ->set('lastName', 'Doe')
        ->set('email', 'john@doe.com')
        ->set('confirmEmail', 'john@doe.com')
        ->set('phone', '555-555-5555')
        ->set('confirmPhone', '555-555-5555')
        ->set('city', 'New York')
        ->set('state', 'New York')
        ->call('handle')
        ->assertEmitted('registeredForCampfire');

    $this->assertDatabaseHas('attendees', [
        'campfire_id' => $campfire->id,
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'john@doe.com',
    ]);

    $this->assertDatabaseHas('model_has_roles', [
        'model_id' => User::where('email', 'john@doe.com')->first()->id,
        'role_id' => 3,
    ]);
});
