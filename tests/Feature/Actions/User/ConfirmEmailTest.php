<?php

use App\Models\User;
use App\Models\EmailVerify;
use App\Actions\User\ConfirmEmail;

it('confirms a users email address', function () {
    $user = User::factory()->unverified()->create();
    $token = EmailVerify::create([
        'user_id' => $user->id,
    ]);

    ConfirmEmail::run(user: $user, token: $token);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email_verified_at' => now(),
    ]);

    $this->assertDatabaseMissing('email_verifies', [
        'id' => $token->id,
    ]);
});
