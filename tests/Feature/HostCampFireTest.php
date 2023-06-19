<?php

use App\Http\Livewire\HostCampfire;
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

test('the Host Fire page renders without error', function (): void {
    $this->get(route('host'))
        ->assertStatus(200);
});

test('Host Fire component renders', function (): void {
    livewire(HostCampfire::class)
        ->assertStatus(200);
});

test('an unauthenticated user is redirected to login trying to access host url', function () {
    auth()->logout();
    $this->get(route('host'))
        ->assertRedirect(route('social.login', ['provider' => 'auth0']));
});
