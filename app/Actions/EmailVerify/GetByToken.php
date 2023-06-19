<?php

namespace App\Actions\EmailVerify;

use App\Models\EmailVerify;
use Lorisleiva\Actions\Concerns\AsAction;

class GetByToken
{
    use AsAction;

    public function handle(string $token): ?EmailVerify
    {
        return EmailVerify::where('token', '=', $token)->first();
    }
}
