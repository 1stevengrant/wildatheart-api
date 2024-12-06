<?php

use App\Models\User;
use App\Models\EmailVerify;
use App\Actions\EmailVerify\DeleteEmailVerify;

it('deletes an email verification', function () {
    $user = User::factory()->create();
    $emailVerify = EmailVerify::create([
        'user_id' => $user->id,
    ]);

    DeleteEmailVerify::run($emailVerify);

    $this->assertDatabaseMissing('email_verifies', [
        'id' => $emailVerify->id,
    ]);
});
