<?php

namespace App\Exceptions;

use Exception;

class EventOwnershipTransferException extends Exception
{
    public static function onlyOwnerCanTransfer(): self
    {
        return new self('Only the event owner can transfer ownership.');
    }

    public static function onlyOwnerCanCancel(): self
    {
        return new self('Only the event owner can cancel an ownership transfer.');
    }

    public static function recipientNotFound(): self
    {
        return new self('User not found. Only existing hosts can be promoted to owner.');
    }

    public static function recipientIsOwner(): self
    {
        return new self('You already own this event.');
    }

    public static function recipientIsNotManager(): self
    {
        return new self('Only existing hosts can be promoted to owner. Please add them as a host first.');
    }

    public static function alreadyPending(): self
    {
        return new self('There is already a pending ownership transfer for this event.');
    }

    public static function noPendingTransfer(): self
    {
        return new self('No pending ownership transfer found.');
    }

    public static function wrongRecipient(): self
    {
        return new self('This ownership transfer is for a different email address.');
    }

    public static function eventUnavailable(): self
    {
        return new self('This event is no longer available.');
    }
}
