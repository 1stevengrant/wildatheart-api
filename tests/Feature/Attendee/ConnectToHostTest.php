<?php

use App\Http\Livewire\ConnectToHost;
use App\Models\Campfire;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

test('a known user can connect to a given Fire', function () {
    $this->withoutExceptionHandling();
    $campfire = Campfire::factory()->approved()->create();
    ray($campfire->toArray());
    $user = User::factory()->create();
    $this->actingAs($user);

    livewire(ConnectToHost::class, ['campfire' => $campfire])
        ->set('firstName', $user->given_name)
        ->set('lastName', $user->family_name)
        ->set('email', $user->email)
        ->set('city', 'New York')
        ->set('state', 'New York')
        ->set('phone', '5555555555')
        ->set('emailUpdates', true)
        ->set('token', '')
        ->set('country', 'United States')
        ->call('handle');

    $this->assertDatabaseHas('attendees', [
        'campfire_id' => $campfire->id,
        'email' => $user->email,
    ]);
});

test('an anonymous user can connect to a given Fire', function () {
    $campfire = Campfire::factory()->approved()->create();

    livewire(ConnectToHost::class, ['campfire' => $campfire])
        ->set('firstName', 'Steven')
        ->set('lastName', 'Grant')
        ->set('email', 'steven@servant.io')
        ->set('city', 'New York')
        ->set('state', 'New York')
        ->set('phone', '5555555555')
        ->set('emailUpdates', true)
        ->set('token', '')
        ->set('country', 'United States')
        ->call('handle')
        ->assertEmitted('connectedToHost');

    $this->assertDatabaseHas('attendees', [
        'campfire_id' => $campfire->id,
    ]);
});
