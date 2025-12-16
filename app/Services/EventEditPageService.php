<?php

namespace App\Services;

use App\Models\Event;
use App\Data\EventData;
use App\Data\EventManagerData;
use Illuminate\Support\Collection;
use App\Data\EventOwnershipTransferData;

class EventEditPageService
{
    public function buildPageData(Event $event): array
    {
        $event->load('user', 'managers', 'pendingManagerInvitations.invitedByUser', 'pendingOwnershipTransfer.transferredByUser');

        return [
            'event' => EventData::fromModel($event),
            'managers' => $this->assembleManagers($event),
            'pendingTransfer' => $event->pendingOwnershipTransfer
                ? EventOwnershipTransferData::fromModel($event->pendingOwnershipTransfer)
                : null,
            'isOwner' => auth()->id() === $event->user_id,
        ];
    }

    private function assembleManagers(Event $event): Collection
    {
        $hosts = collect();

        // Add the owner first (unless they are the current user viewing)
        if ($event->user && $event->user->id !== auth()->id()) {
            $hosts->push(EventManagerData::fromUser($event->user, isOwner: true));
        }

        // Add existing managers (excluding current user)
        $existingManagers = $event->managers
            ->reject(fn ($manager) => $manager->id === auth()->id())
            ->map(fn ($manager) => EventManagerData::fromUser($manager))
            ->values();

        $hosts = $hosts->merge($existingManagers);

        // Add pending invitations (excluding current user's email)
        $pendingInvitations = $event->pendingManagerInvitations
            ->reject(fn ($invitation) => $invitation->email === auth()->user()?->email)
            ->map(fn ($invitation) => EventManagerData::fromInvitation($invitation))
            ->values();

        return $hosts->merge($pendingInvitations)->values();
    }
}
