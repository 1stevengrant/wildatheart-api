<?php

use App\Actions\User\CreateUser;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Bus::fake();
    Mail::fake();
});

it('creates a new user', function () {
    $userAttributes = [
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'steven@example.com',
        'password' => 'password',
    ];

    $user = CreateUser::run($userAttributes);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'steven@example.com']);
});
