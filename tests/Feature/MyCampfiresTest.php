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

test('the my 🔥 page renders without error', function (): void {
    $this->get(route('my.campfires'))
        ->assertStatus(200);
});

test('My 🔥 component renders', function (): void {
    livewire(MyCampfires::class)
        ->assertStatus(200);
});

test('user can see their list of 🔥', function () {
    $campfires = Campfire::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ]);

    livewire(MyCampfires::class)
        ->assertSee($campfires->first()->description)
        ->assertSee($campfires->last()->description);
});

test('user cannot see another users 🔥', function () {
    $otherUser = User::factory()->create();
    $campfires = Campfire::factory()->count(3)->create([
        'user_id' => $otherUser->id,
    ]);
    livewire(MyCampfires::class)
        ->assertDontSee($campfires->first()->description)
        ->assertDontSee($campfires->last()->description);
});

test('user cannot edit/delete 🔥 in the past', function () {
    Campfire::factory()->create([
        'user_id' => $this->user->id,
        'date' => now()->subDay(),
    ]);

    livewire(MyCampfires::class)
        ->assertDontSee('Edit')
        ->assertDontSee('Delete');
});

test('user sees pending status for not approved 🔥 in the future', function () {
    Campfire::factory()->create([
        'user_id' => $this->user->id,
        'approved_at' => null,
        'date' => now()->addDay(),
    ]);

    livewire(MyCampfires::class)
        ->assertSee('Pending');
});

test('user see edit/delete for upcoming 🔥', function () {
    Campfire::factory()->create([
        'user_id' => $this->user->id,
        'date' => now()->addDay(),
    ]);

    livewire(MyCampfires::class)
        ->assertSee('Edit')
        ->assertSee('Delete');
});

test('user can see request more books for their 🔥 if approved', function () {
    Campfire::factory()->inTheFuture()->approved()->create([
        'user_id' => $this->user->id,
    ]);
    livewire(MyCampfires::class)
        ->assertSee('Request More Books');
});
