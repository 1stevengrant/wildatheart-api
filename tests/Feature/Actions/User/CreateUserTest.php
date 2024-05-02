<?php

use App\Actions\User\CreateUser;

it('creates a new user', function () {
    $userAttributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'steven@ghijk.co.uk',
        'password' => 'password',
    ];

    $user = CreateUser::run($userAttributes);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'steven@ghijk.co.uk']);
});
