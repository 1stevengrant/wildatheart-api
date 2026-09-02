<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Mail\HostInvitationMail;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendPendingCoHostInvitations
{
    use AsAction;

    public function handle(Event $event, User $invitedBy): void
    {
        $event->loadMissing('pendingManagerInvitations');

        foreach ($event->pendingManagerInvitations as $invitation) {
            Mail::to($invitation->email)->send(
                new HostInvitationMail($event, $invitedBy->fullname, $invitation)
            );
        }
    }
}
