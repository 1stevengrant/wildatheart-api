<?php

namespace App\Actions\User;

use App\Mail\Event\SendEmailVerificationEmail;
use App\Models\Event;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendVerificationEmail
{
    use AsAction;

    public function handle(Event $event): void
    {
        $verification = auth()->user()->emailVerifications()->create([
            'event_id' => $event->id,
        ]);

        activity()
            ->performedOn($event)
            ->causedBy(auth()->user())
            ->log('Send Verification Email');

        Mail::to(auth()->user()->email)->send(new SendEmailVerificationEmail(
            verificationToken: $verification->token,
            user: auth()->user(),
        ));
    }
}
