<?php

namespace App\Actions\Event;

use App\Models\User;
use App\Models\Event;
use App\Mail\HostRemovedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\EventManagerInvitation;
use App\Exceptions\EventManagerException;
use Lorisleiva\Actions\Concerns\AsAction;
use App\DataTransferObjects\EventManagerRemoved;

class RemoveEventManager
{
    use AsAction;

    /**
     * Resolves the identifier as a pending invitation first, then as a host's user id.
     *
     * @throws EventManagerException
     */
    public function handle(Event $event, User $remover, string $managerId): EventManagerRemoved
    {
        $result = DB::transaction(function () use ($event, $managerId): EventManagerRemoved {
            $lockedEvent = Event::query()->lockForUpdate()->findOrFail($event->getKey());

            $invitation = EventManagerInvitation::query()
                ->where('event_id', $lockedEvent->id)
                ->whereKey($managerId)
                ->first();

            if ($invitation) {
                $invitation->delete();

                return EventManagerRemoved::invitationCancelled($invitation);
            }

            $manager = ctype_digit($managerId)
                ? User::find($managerId)
                : null;

            if (! $manager) {
                throw EventManagerException::managerNotFound();
            }

            if ($manager->id === $lockedEvent->user_id) {
                throw EventManagerException::cannotRemoveOwner();
            }

            if (! $lockedEvent->managers()->where('user_id', $manager->id)->exists()) {
                throw EventManagerException::notAManager();
            }

            $lockedEvent->managers()->detach($manager->id);

            return EventManagerRemoved::managerRemoved($manager);
        });

        if (! $result->removedManager) {
            return $result;
        }

        $event->refresh();

        Mail::to($result->removedManager->email)->send(new HostRemovedMail($event, $result->removedManager, $remover));

        if ($remover->id !== $event->user_id) {
            Mail::to($event->user->email)->send(new HostRemovedMail($event, $result->removedManager, $remover));
        }

        return $result;
    }
}
