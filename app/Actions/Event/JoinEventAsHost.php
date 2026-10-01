<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Models\EventManagerInvitation;
use Lorisleiva\Actions\Concerns\AsAction;

class JoinEventAsHost
{
    use AsAction;

    /**
     * Add a user as a co-host through the event's shareable invite link.
     *
     * Reuses a pending email invitation when one exists, otherwise records one,
     * so joining by link goes through the same acceptance path as email invites.
     */
    public function handle(Event $event, User $user): void
    {
        if ($event->isOwnedOrManagedBy($user)) {
            return;
        }

        $email = mb_strtolower($user->email);

        $invitation = $event->pendingManagerInvitations()->where('email', $email)->first()
            ?? EventManagerInvitation::create([
                'event_id' => $event->id,
                'email' => $email,
                'invited_by_user_id' => $event->user_id,
            ]);

        AcceptCoHostInvitation::run($invitation, $user);
    }
}
