<?php

use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
beforeEach(function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);
});
it('checks if a user should be an admin', function () {
    $user = User::factory()->create();
    \App\Jobs\CheckIfUserShouldBeAdmin::dispatch($user);
    expect($user->hasRole(User::ROLE_ADMIN))->toBeFalse();
});

it('checks if a user with @ghijk.co.uk email address should be an admin', function () {
    $user = User::factory()->create([
        'email' => 'steven@ghijk.co.uk',
    ]);

    $user2 = User::factory()->create([
        'email' => 'steven@wildatheart.org',
    ]);

    \App\Jobs\CheckIfUserShouldBeAdmin::dispatch($user);
    \App\Jobs\CheckIfUserShouldBeAdmin::dispatch($user2);

    expect($user->hasRole(User::ROLE_ADMIN))->toBeTrue()
        ->and($user2->hasRole(User::ROLE_ADMIN))->toBeTrue();
});
