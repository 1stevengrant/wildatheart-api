<?php

use App\Models\User;
use App\Enums\UserRoleEnum;
use Database\Seeders\DatabaseSeeder;
use App\Jobs\Admin\CheckIfUserShouldBeAdmin;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});
it('checks if a user should be an admin', function () {
    $user = User::factory()->create([
        'email' => 'steven@msn.com',
    ]);
    CheckIfUserShouldBeAdmin::dispatch($user);
    expect($user->hasRole(UserRoleEnum::Admin))->toBeFalse();
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

    expect($user->hasRole(UserRoleEnum::Admin))->toBeTrue()
        ->and($user2->hasRole(UserRoleEnum::Admin))->toBeTrue();
});
