<?php

use App\Actions\User\UpdateUserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
it('updates a user role', function () {
    Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
    $user = User::factory()->create();
    UpdateUserRole::run($user, 'admin');

    expect($user->fresh()->hasRole('admin'))->toBeTrue();
});
