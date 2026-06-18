<?php

namespace App\Enums\Internal;

use App\Builders\EventBuilder;

enum EventVisibilityFilter: string
{
    case All = 'all';
    case Public = 'public';
    case Private = 'private';

    public function applyTo(EventBuilder $query): EventBuilder
    {
        return match ($this) {
            self::All => $query,
            self::Public => $query->public(),
            self::Private => $query->private(),
        };
    }
}
