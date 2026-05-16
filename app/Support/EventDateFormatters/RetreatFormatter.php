<?php

namespace App\Support\EventDateFormatters;

use App\Models\Event;

class RetreatFormatter
{
    public static function format(Event $event, int $currentYear): ?string
    {
        if (! $event->start_date || ! $event->end_date) {
            return null;
        }

        if ($event->start_date->isSameDay($event->end_date)) {
            $format = $event->start_date->year !== $currentYear ? 'M jS Y' : 'M jS';

            return $event->start_date->format($format);
        }

        if ($event->start_date->month === $event->end_date->month && $event->start_date->year === $event->end_date->year) {
            $endFormat = $event->end_date->year !== $currentYear ? 'jS Y' : 'jS';

            return $event->start_date->format('M jS') . '—' . $event->end_date->format($endFormat);
        }

        $endFormat = $event->end_date->year !== $currentYear ? 'M jS Y' : 'M jS';

        return $event->start_date->format('M jS') . '—' . $event->end_date->format($endFormat);
    }
}
