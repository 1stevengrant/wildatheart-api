<?php

namespace App\Actions\User;

use App\Mail\Fires\SendEmailVerificationEmail;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendVerificationEmail
{
    use AsAction;

    public function handle(): void
    {
        $verification = auth()->user()->emailVerifications()->create();

        Mail::to(auth()->user()->email)->send(new SendEmailVerificationEmail($verification->token));
    }
}
