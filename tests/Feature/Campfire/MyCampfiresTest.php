<?php

use App\Http\Livewire\MyCampfires;
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

test('the my Fire page renders without error', function (): void {
    $this->get(route('my.campfires'))
        ->assertStatus(200);
});

test('My Fire component renders', function (): void {
    livewire(MyCampfires::class)
        ->assertStatus(200);
});

test('user can see their list of Fire', function () {
    $campfires = Campfire::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ]);

    livewire(MyCampfires::class)
        ->assertSee($campfires->first()->name)
        ->assertSee($campfires->last()->name);
});

test('user cannot see another users Fire', function () {
    $otherUser = User::factory()->create();
    $campfires = Campfire::factory()->count(3)->create([
        'user_id' => $otherUser->id,
    ]);
    livewire(MyCampfires::class)
        ->assertDontSee($campfires->first()->name)
        ->assertDontSee($campfires->last()->name);
});
