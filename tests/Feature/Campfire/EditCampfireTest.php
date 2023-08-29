<?php

use App\Http\Livewire\EditCampfire;
use App\Models\Campfire;
use App\Models\User;
use App\Rules\ShouldNotEndWithFire;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Repat\LaravelRules\DoesntContainEmojis;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole(User::ROLE_HOST);
    actingAs($this->user);
    $this->campfire = Campfire::factory()->approved()->create([
        'user_id' => $this->user->id,
    ]);
});

test('a user can edit their own Fire', function () {

    livewire(EditCampfire::class, ['campfire' => $this->campfire])
        ->set('campfire.name', 'My New Name')
        ->set('campfire.latitude', fake()->latitude())
        ->set('campfire.longitude', fake()->longitude())
        ->call('handle')
        ->assertEmitted('campfireUpdated')
        ->assertRedirect(route('my.campfires'));

    $this->assertDatabaseHas('campfires', [
        'id' => $this->campfire->id,
        'name' => 'My New Name',
    ]);
});

test('a Fire name cannot end in the word "Fire" ', function () {

    livewire(EditCampfire::class, ['campfire' => $this->campfire])
        ->set('campfire.name', 'My New Fire')
        ->set('campfire.latitude', fake()->latitude())
        ->set('campfire.longitude', fake()->longitude())
        ->call('handle')
        ->assertHasErrors(['campfire.name' => ShouldNotEndWithFire::class]);
});

test('a Fire name cannot end in the word "fire" in lowercase ', function () {

    livewire(EditCampfire::class, ['campfire' => $this->campfire])
        ->set('campfire.name', 'My New fire')
        ->set('campfire.latitude', fake()->latitude())
        ->set('campfire.longitude', fake()->longitude())
        ->call('handle')
        ->assertHasErrors(['campfire.name' => ShouldNotEndWithFire::class]);
});

test('a Fire name cannot contain an emoji', function () {
    livewire(EditCampfire::class, ['campfire' => $this->campfire])
        ->set('campfire.name', 'My New 🔥')
        ->set('campfire.latitude', fake()->latitude())
        ->set('campfire.longitude', fake()->longitude())
        ->call('handle')
        ->assertHasErrors(['campfire.name' => DoesntContainEmojis::class]);
});
