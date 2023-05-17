<?php

use App\Http\Livewire\EditFire;
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

test('a user can edit their own 🔥', function () {
    $campfire = Campfire::factory()->create([
        'user_id' => $this->user->id,
    ]);

    livewire(EditFire::class, ['fire' => $campfire])
        ->set('campfire.address', '18 Greenlees Road')
        ->call('handle')
        ->assertEmitted('fireUpdated')
        ->assertDispatchedBrowserEvent('notify', 'Campfire updated successfully 🔥');

    $this->assertDatabaseHas('camp_fires', [
        'id' => $campfire->id,
        'address' => '18 Greenlees Road',
    ]);
});
