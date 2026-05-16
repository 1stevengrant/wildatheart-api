<?php

namespace App\Support\EventDateFormatters;

use App\Models\Event;

class DefaultFormatter
{
    public static function format(Event $event, int $currentYear): string
    {
        if (! $event->start_date) {
            return '';
        }

        $format = $event->start_date->year !== $currentYear ? 'F jS Y' : 'F jS';

        return $event->start_date->format($format);
    }
}
