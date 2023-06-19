<?php

use App\Http\Livewire\EditCampfire;
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

test('a user can edit their own Fire', function () {
    $campfire = Campfire::factory()->create([
        'user_id' => $this->user->id,
    ]);

    livewire(EditCampfire::class, ['campfire' => $campfire])
        ->set('campfire.name', 'My Fire')
        ->call('handle')
        ->assertEmitted('campfireUpdated')
        ->assertDispatchedBrowserEvent('notify', 'Campfire updated successfully Fire');

    $this->assertDatabaseHas('campfires', [
        'id' => $campfire->id,
        'name' => 'My Fire',
    ]);
})->skip();
