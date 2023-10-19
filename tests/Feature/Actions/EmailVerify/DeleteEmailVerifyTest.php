<?php

use App\Actions\EmailVerify\DeleteEmailVerify;
use App\Models\EmailVerify;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
