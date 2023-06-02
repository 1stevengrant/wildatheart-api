<?php

use App\Http\Livewire\ConnectToHost;
use App\Models\Campfire;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Livewire\livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

test('a known user can connect to a given Fire', function () {
    $campfire = Campfire::factory()->inTheFuture()->approved()->create();
    $user = User::factory()->create();
    $this->actingAs($user);

    livewire(ConnectToHost::class, ['campfire' => $campfire])
        ->call('handle');

    $this->assertDatabaseHas('attendees', [
        'user_id' => auth()->id(),
        'campfire_id' => $campfire->id,
    ]);
});

test('an anonymous user can connect to a given Fire', function () {
    $campfire = Campfire::factory()->inTheFuture()->approved()->create();

    livewire(ConnectToHost::class, ['campfire' => $campfire])
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
        'role_id' => Role::where('name', User::ROLE_ATTENDEE)->first()->id,
    ]);
});
