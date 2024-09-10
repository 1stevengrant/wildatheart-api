<?php

use App\Models\User;

beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});
test('full name returns the users email address if they have no first name or last name defined', function () {
    $user = User::factory()->create([
        'given_name' => null,
        'family_name' => null,
    ]);
    expect($user->fullname)->toBe($user->email);
});

it('returns given name and family name as the fullname if both are defined', function () {
    $user = User::factory()->create([
        'given_name' => 'John',
        'family_name' => 'Doe',
    ]);

    expect($user->fullname)->toBe('John Doe');
});
