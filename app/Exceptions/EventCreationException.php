<?php

namespace App\Exceptions;

use Exception;

class EventCreationException extends Exception
{
    public static function duplicate(): self
    {
        return new self('An event was just created. Please wait a moment before creating another.');
    }
}
