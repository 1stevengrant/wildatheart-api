<?php

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use App\Jobs\Admin\CheckIfUserShouldBeAdmin;

beforeEach(function () {
    Bus::fake()->except(CheckIfUserShouldBeAdmin::class);
    Mail::fake();
});

it('does not assign admin role to a user with a non-admin email', function () {
    $user = User::factory()->create([
        'email' => 'steven@example.com',
    ]);

    CheckIfUserShouldBeAdmin::dispatchSync($user);

    expect($user->hasRole(UserRoleEnum::Admin))->toBeFalse();
});

it('assigns admin role to a user with @ghijk.co.uk email', function () {
    $user = User::factory()->create([
        'email' => 'steven@ghijk.co.uk',
    ]);

    CheckIfUserShouldBeAdmin::dispatchSync($user);

    expect($user->hasRole(UserRoleEnum::Admin))->toBeTrue();
});

it('assigns admin role to a user with @wildatheart.org email', function () {
    $user = User::factory()->create([
        'email' => 'steven@wildatheart.org',
    ]);

    CheckIfUserShouldBeAdmin::dispatchSync($user);

    expect($user->hasRole(UserRoleEnum::Admin))->toBeTrue();
});
