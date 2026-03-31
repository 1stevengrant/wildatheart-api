<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case Admin = 'admin';
    case Host = 'host';
    case Attendee = 'attendee';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Host => 'Host',
            self::Attendee => 'Attendee',
        };
    }
}
