<?php

namespace App\Actions\User;

use App\Actions\EmailVerify\DeleteEmailVerify;
use App\Models\EmailVerify;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

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
