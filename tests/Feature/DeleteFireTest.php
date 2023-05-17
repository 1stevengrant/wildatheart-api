<?php

use App\Http\Livewire\DeleteFire;
use App\Models\Campfire;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->user = User::factory()->create();
    $this->user->assignRole(User::ROLE_HOST);
    actingAs($this->user);
});

test('Delete 🔥 component renders', function (): void {
    livewire(DeleteFire::class)
        ->assertStatus(200);
});

test('a user can delete their own 🔥', function () {
    $campfire = Campfire::factory()->create([
        'user_id' => $this->user->id,
        'deleted_at' => null,
    ]);

    livewire(DeleteFire::class, ['fire' => $campfire])
        ->call('handle')
        ->assertEmitted('fireDeleted')
        ->assertDispatchedBrowserEvent('notify', '🔥 deleted successfully!');

    $this->assertSoftDeleted('camp_fires', ['id' => $campfire->id]);
});

test('a user cannot delete the 🔥 of another user', function () {
    $user2 = User::factory()->create();
    $campfire = Campfire::factory()->create([
        'user_id' => $user2->id,
    ]);

    livewire(DeleteFire::class, ['fire' => $campfire])
        ->call('handle')
        ->assertNotEmitted('fireDeleted')
        ->assertNotDispatchedBrowserEvent('notify');

    $this->assertDatabaseHas('camp_fires', ['id' => $campfire->id]);
})->skip();
