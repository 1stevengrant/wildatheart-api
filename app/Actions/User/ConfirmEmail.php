<?php

namespace App\Actions\User;

use App\Models\User;
use App\Models\EmailVerify;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Actions\EmailVerify\DeleteEmailVerify;

class ConfirmEmail
{
    use AsAction;

    public function handle(
        User $user,
        EmailVerify $token
    ): void {

        $user->update([
            'email_verified_at' => now(),
        ]);

        DeleteEmailVerify::run(token: $token);
    }
}
