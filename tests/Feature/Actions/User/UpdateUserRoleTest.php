<?php

use App\Actions\User\UpdateUserRole;
use App\Models\User;

it('updates a user role', function () {
    Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
    $user = User::factory()->create();
    UpdateUserRole::run($user, User::ROLE_ADMIN);

    expect($user->fresh()->hasRole(User::ROLE_ADMIN))->toBeTrue();
});
