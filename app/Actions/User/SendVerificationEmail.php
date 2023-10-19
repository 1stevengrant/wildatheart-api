<?php

namespace App\Actions\User;

use App\Mail\Campfire\SendEmailVerificationEmail;
use App\Models\Campfire;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendVerificationEmail
{
    use AsAction;

    public function handle(Campfire $campfire): void
    {

        $verification = auth()->user()->emailVerifications()->create([
            'campfire_id' => $campfire->id,
        ]);

        Mail::to(auth()->user()->email)->send(new SendEmailVerificationEmail(
            verificationToken: $verification->token,
            user: auth()->user(),
        ));
    }
}
