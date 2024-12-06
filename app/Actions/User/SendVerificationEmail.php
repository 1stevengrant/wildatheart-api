<?php

namespace App\Actions\User;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Mail\Event\SendEmailVerificationEmail;

class SendVerificationEmail
{
    use AsAction;

    public function handle(Event $event): void
    {
        $verification = auth()->user()->emailVerifications()->create([
            'event_id' => $event->id,
        ]);

        dispatch(new LogActivityJob(
            description: 'Send Verification Email',
            causedBy: auth()->user(),
            performedOn: $event,
        ));

        Mail::to(auth()->user()->email)->send(new SendEmailVerificationEmail(
            verificationToken: $verification->token,
            user: auth()->user(),
        ));
    }
}
