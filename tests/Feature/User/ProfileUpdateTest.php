<?php

use App\Http\Livewire\MyProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

test('a user can update their profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user);
    livewire(MyProfile::class)
        ->set('user.given_name', 'Steven')
        ->set('user.family_name', 'Grant')
        ->set('user.phone', '999999999')
        ->set('user.country_code', '44')
        ->set('user.opted_into_marketing', true)
        ->call('handle');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'given_name' => 'Steven',
        'family_name' => 'Grant',
        'country_code' => '44',
        'phone' => '999999999',
    ]);
});
