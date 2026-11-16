<?php

namespace App\DataTransferObjects;

use App\Models\User;
use App\Models\EventManagerInvitation;

readonly class EventManagerRemoved
{
    private function __construct(
        public ?EventManagerInvitation $cancelledInvitation,
        public ?User $removedManager,
    ) {}

    public static function invitationCancelled(EventManagerInvitation $invitation): self
    {
        return new self(cancelledInvitation: $invitation, removedManager: null);
    }

    public static function managerRemoved(User $manager): self
    {
        return new self(cancelledInvitation: null, removedManager: $manager);
    }

    public function message(): string
    {
        if ($this->cancelledInvitation) {
            return "Invitation to {$this->cancelledInvitation->email} has been cancelled.";
        }

        return "{$this->removedManager->fullname} has been removed as a host.";
    }
}
