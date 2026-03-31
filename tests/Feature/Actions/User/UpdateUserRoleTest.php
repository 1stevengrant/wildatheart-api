<?php

use App\Models\User;
use App\Enums\UserRoleEnum;
use App\Actions\User\UpdateUserRole;

it('updates a user role', function () {
    Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
    $user = User::factory()->create();
    UpdateUserRole::run($user, UserRoleEnum::Admin);

    expect($user->fresh()->hasRole(UserRoleEnum::Admin))->toBeTrue();
});
