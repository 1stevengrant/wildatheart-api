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
})->skip();

test('My Fire component renders', function (): void {
    livewire(MyCampfires::class)
        ->assertStatus(200);
})->skip();

test('user can see their list of Fire', function () {
    $campfires = Campfire::factory()->count(3)->create([
        'user_id' => $this->user->id,
    ]);

    livewire(MyCampfires::class)
        ->assertSee($campfires->first()->description)
        ->assertSee($campfires->last()->description);
})->skip();

test('user cannot see another users Fire', function () {
    $otherUser = User::factory()->create();
    $campfires = Campfire::factory()->count(3)->create([
        'user_id' => $otherUser->id,
    ]);
    livewire(MyCampfires::class)
        ->assertDontSee($campfires->first()->description)
        ->assertDontSee($campfires->last()->description);
})->skip();

test('user cannot edit/delete Fire in the past', function () {
    Campfire::factory()->create([
        'user_id' => $this->user->id,
        'date' => now()->subDay(),
    ]);

    livewire(MyCampfires::class)
        ->assertDontSee('Edit')
        ->assertDontSee('Delete');
})->skip();

test('user sees pending status for not approved Fire in the future', function () {
    Campfire::factory()->create([
        'user_id' => $this->user->id,
        'approved_at' => null,
        'date' => now()->addDay(),
    ]);

    livewire(MyCampfires::class)
        ->assertSee('Pending');
})->skip();

test('user see edit/delete for upcoming Fire', function () {
    Campfire::factory()->create([
        'user_id' => $this->user->id,
        'date' => now()->addDay(),
    ]);

    livewire(MyCampfires::class)
        ->assertSee('Edit')
        ->assertSee('Delete');
})->skip();

test('user can see request more books for their Fire if approved', function () {
    Campfire::factory()->inTheFuture()->approved()->create([
        'user_id' => $this->user->id,
    ]);
    livewire(MyCampfires::class)
        ->assertSee('Request More Books');
})->skip();
