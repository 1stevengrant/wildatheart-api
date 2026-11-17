<?php

namespace App\DataTransferObjects;

use App\Models\User;
use App\Models\EventOwnershipTransfer;

readonly class EventOwnershipTransferInitiated
{
    public function __construct(
        public EventOwnershipTransfer $transfer,
        public User $recipient,
    ) {}
}
