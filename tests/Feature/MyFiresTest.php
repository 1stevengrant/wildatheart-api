<?php

use App\Http\Livewire\MyFires;
use App\Models\CampFire;
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
    $this->get(route('my.fires'))
        ->assertStatus(200);
});

test('My 🔥 component renders', function (): void {
    livewire(MyFires::class)
        ->assertStatus(200);
});

test('user can see their list of 🔥', function () {
   $fires = CampFire::factory()->count(3)->create([
       'user_id' => $this->user->id
   ]);

   livewire(MyFires::class)
       ->assertSee($fires->first()->description)
       ->assertSee($fires->last()->description);
});

test('user cannot see another users 🔥', function () {
    $otherUser = User::factory()->create();
    $fires = CampFire::factory()->count(3)->create([
        'user_id' => $otherUser->id
    ]);
    livewire(MyFires::class)
        ->assertDontSee($fires->first()->description)
        ->assertDontSee($fires->last()->description);
});

test('user cannot edit/delete 🔥 in the past', function () {
    CampFire::factory()->create([
        'user_id' => $this->user->id,
        'date' => now()->subDay()
    ]);

    livewire(MyFires::class)
        ->assertDontSee('Edit')
        ->assertDontSee('Delete');
});

test('user sees pending status for not approved 🔥', function () {
    CampFire::factory()->create([
        'user_id' => $this->user->id,
    ]);
    livewire(MyFires::class)
        ->assertSee('Pending');
});

test('user see edit/delete for upcoming 🔥', function () {
    CampFire::factory()->create([
        'user_id' => $this->user->id,
        'date' => now()->addDay()
    ]);

    livewire(MyFires::class)
        ->assertSee('Edit')
        ->assertSee('Delete');
});

test('user can see request more books for their 🔥 if approved', function () {
    CampFire::factory()->approved()->create([
        'user_id' => $this->user->id,
    ]);
    livewire(MyFires::class)
        ->assertSee('Request More Books');
});
