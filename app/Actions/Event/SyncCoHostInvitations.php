<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Collection;
use App\Models\EventManagerInvitation;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncCoHostInvitations
{
    use AsAction;

    /**
     * @param  array<int, string>  $emails
     * @return Collection<int, EventManagerInvitation> Newly created invitations.
     */
    public function handle(Event $event, array $emails, User $invitedBy): Collection
    {
        $candidateEmails = collect($emails)
            ->map(fn (string $email) => mb_strtolower(mb_trim($email)))
            ->filter()
            ->unique()
            ->reject(fn (string $email) => $email === mb_strtolower($event->user?->email ?? ''));

        $existingUsers = User::whereIn('email', $candidateEmails->all())
            ->get()
            ->keyBy('email');

        $managerUserIds = $event->managers()->pluck('user_id')->all();

        $validEmails = $candidateEmails
            ->reject(function (string $email) use ($event, $existingUsers, $managerUserIds) {
                $user = $existingUsers->get($email);

                if (! $user) {
                    return false;
                }

                return $user->id === $event->user_id
                    || in_array($user->id, $managerUserIds);
            })
            ->values();

        $staleInvitations = $event->pendingManagerInvitations();

        if ($validEmails->isNotEmpty()) {
            $staleInvitations->whereNotIn('email', $validEmails->all());
        }

        $staleInvitations->get()->each(fn (EventManagerInvitation $invitation) => $invitation->delete());

        $newInvitations = new Collection;

        foreach ($validEmails as $email) {
            if ($event->pendingManagerInvitations()->where('email', $email)->exists()) {
                continue;
            }

            $newInvitations->push(EventManagerInvitation::create([
                'event_id' => $event->id,
                'email' => $email,
                'invited_by_user_id' => $invitedBy->id,
            ]));
        }

        return $newInvitations;
    }
}
