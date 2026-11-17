<?php

namespace App\Exceptions;

use Exception;

class EventManagerException extends Exception
{
    public static function candidateIsOwner(): self
    {
        return new self('This user is already the event owner.');
    }

    public static function candidateIsManager(): self
    {
        return new self('This user is already a host for this event.');
    }

    public static function alreadyInvited(): self
    {
        return new self('An invitation has already been sent to this email address.');
    }

    public static function cannotRemoveOwner(): self
    {
        return new self('Cannot remove the event owner.');
    }

    public static function notAManager(): self
    {
        return new self('This user is not a host for this event.');
    }

    public static function managerNotFound(): self
    {
        return new self('Host or invitation not found.');
    }

    public static function wrongRecipient(): self
    {
        return new self('This invitation is for a different email address.');
    }

    public static function eventUnavailable(): self
    {
        return new self('This event is no longer available.');
    }
}
