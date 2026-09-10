<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Lorisleiva\Actions\Concerns\AsAction;

class SendPendingCoHostInvitations
{
    use AsAction;

    public function handle(Event $event, User $invitedBy): void
    {
        $event->loadMissing('pendingManagerInvitations');

        SendCoHostInvitationEmails::run($event->pendingManagerInvitations, $event, $invitedBy);
    }
}
