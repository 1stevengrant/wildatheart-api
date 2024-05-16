<?php

use App\Jobs\CheckIfUserShouldBeAdmin;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});
it('checks if a user should be an admin', function () {
    $user = User::factory()->create([
        'email' => 'steven@msn.com',
    ]);
    CheckIfUserShouldBeAdmin::dispatch($user);
    expect($user->hasRole(User::ROLE_ADMIN))->toBeFalse();
});

it('checks if a user with @ghijk.co.uk email address should be an admin', function () {
    $user = User::factory()->create([
        'email' => 'steven@ghijk.co.uk',
    ]);

    $user2 = User::factory()->create([
        'email' => 'steven@wildatheart.org',
    ]);

    CheckIfUserShouldBeAdmin::dispatch($user);
    CheckIfUserShouldBeAdmin::dispatch($user2);

    expect($user->hasRole(User::ROLE_ADMIN))->toBeTrue()
        ->and($user2->hasRole(User::ROLE_ADMIN))->toBeTrue();
});
