<?php

use App\Models\User;
use App\Models\EmailVerify;

it('gets an email verification by its token code', function () {
    $user = User::factory()->create();
    $emailVerify = EmailVerify::create([
        'user_id' => $user->id,
    ]);
    $action = \App\Actions\EmailVerify\GetByToken::run($emailVerify->token);

    $this->assertDatabaseHas('email_verifies', [
        'id' => $emailVerify->id,
    ]);

    expect($action)->toBeInstanceOf(EmailVerify::class)
        ->and($action->id)->toBe($emailVerify->id);
});
