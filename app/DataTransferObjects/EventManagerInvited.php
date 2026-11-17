<?php

namespace App\DataTransferObjects;

use App\Models\User;
use App\Models\EventManagerInvitation;

readonly class EventManagerInvited
{
    public function __construct(
        public EventManagerInvitation $invitation,
        public ?User $invitee,
    ) {}

    public function message(): string
    {
        if ($this->invitee) {
            return "An invitation has been sent to {$this->invitee->fullname}. They will become a host once they accept.";
        }

        return "An invitation has been sent to {$this->invitation->email}. They will become a host once they create an account and accept.";
    }
}
