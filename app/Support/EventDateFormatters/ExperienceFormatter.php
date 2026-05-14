<?php

namespace App\Support\EventDateFormatters;

use App\Models\Event;

class ExperienceFormatter
{
    public static function format(Event $event, int $currentYear): ?string
    {
        if (! $event->start_date) {
            return null;
        }

        $format = $event->start_date->year !== $currentYear ? 'M jS Y' : 'M jS';

        return 'Starts ' . $event->start_date->format($format);
    }
}
