<?php

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Support\Facades\Bus;
use App\Actions\User\UpdateUserRole;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('updates a user role', function () {
    $user = User::factory()->create();
    UpdateUserRole::run($user, UserRoleEnum::Admin);

    expect($user->fresh()->hasRole(UserRoleEnum::Admin))->toBeTrue();
});
