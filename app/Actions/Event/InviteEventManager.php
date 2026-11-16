<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Mail\HostInvitationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Exceptions\EventManagerException;
use Lorisleiva\Actions\Concerns\AsAction;
use App\DataTransferObjects\EventManagerInvited;

class InviteEventManager
{
    use AsAction;

    /** @throws EventManagerException */
    public function handle(Event $event, User $inviter, string $email): EventManagerInvited
    {
        $email = mb_strtolower(mb_trim($email));

        $invitee = User::query()->where('email', $email)->first();

        $invitation = DB::transaction(function () use ($event, $inviter, $email, $invitee): EventManagerInvitation {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->getKey());

            if ($invitee?->id === $lockedEvent->user_id) {
                throw EventManagerException::candidateIsOwner();
            }

            if ($invitee && $lockedEvent->managers()->where('user_id', $invitee->id)->exists()) {
                throw EventManagerException::candidateIsManager();
            }

            if ($lockedEvent->pendingManagerInvitations()->where('email', $email)->exists()) {
                throw EventManagerException::alreadyInvited();
            }

            $lockedEvent->deleteAcceptedManagerInvitation($email);

            return EventManagerInvitation::create([
                'event_id' => $lockedEvent->id,
                'email' => $email,
                'invited_by_user_id' => $inviter->id,
            ]);
        });

        Mail::to($email)->send(new HostInvitationMail($event, $inviter->fullname, $invitation));

        return new EventManagerInvited($invitation, $invitee);
    }
}
