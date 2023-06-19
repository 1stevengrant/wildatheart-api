<?php

namespace App\Actions\EmailVerify;

use App\Models\EmailVerify;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteEmailVerify
{
    use AsAction;

    public function handle(EmailVerify $token): void
    {
        $token->delete();
    }
}
