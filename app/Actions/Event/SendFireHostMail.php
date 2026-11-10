<?php

namespace App\Actions\Event;

use App\Models\Event;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Sends to the Fire's owner with accepted co-hosts CC'd. Pending invitees are not
 * co-hosts yet, so they get nothing.
 */
class SendFireHostMail
{
    use AsAction;

    public function handle(Event $event, Mailable $mailable): void
    {
        $mail = Mail::to($event->user->email);

        if ($event->managers->isNotEmpty()) {
            $mail->cc($event->managers->pluck('email'));
        }

        $mail->send($mailable);
    }
}
