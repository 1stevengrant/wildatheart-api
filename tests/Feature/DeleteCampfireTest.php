<?php

use App\Http\Livewire\DeleteCampfire;
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

test('Delete Fire component renders', function (): void {
    livewire(DeleteCampfire::class)
        ->assertStatus(200);
});

test('a user can delete their own Fire', function () {
    $campfire = Campfire::factory()->create([
        'user_id' => $this->user->id,
        'deleted_at' => null,
    ]);

    livewire(DeleteCampfire::class, ['campfire' => $campfire])
        ->call('handle')
        ->assertEmitted('campfireDeleted')
        ->assertDispatchedBrowserEvent('notify', 'Fire deleted successfully!');

    $this->assertSoftDeleted('campfires', ['id' => $campfire->id]);
});

test('a user cannot delete the Fire of another user', function () {
    $user2 = User::factory()->create();
    $campfire = Campfire::factory()->create([
        'user_id' => $user2->id,
    ]);

    livewire(DeleteCampfire::class, ['campfire' => $campfire])
        ->call('handle')
        ->assertNotEmitted('campfireDeleted')
        ->assertNotDispatchedBrowserEvent('notify');

    $this->assertDatabaseHas('campfires', ['id' => $campfire->id]);
})->skip();
