<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Collection;
use App\Mail\V1\HostInvitationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use Lorisleiva\Actions\Concerns\AsAction;

class SendCoHostInvitationEmails
{
    use AsAction;

    /** @param Collection<int, EventManagerInvitation> $invitations */
    public function handle(Collection $invitations, Event $event, User $invitedBy): void
    {
        foreach ($invitations as $invitation) {
            Mail::to($invitation->email)->send(
                new HostInvitationMail($event, $invitedBy->fullname, $invitation)
            );
        }
    }
}
