<?php

namespace App\Support\EventDateFormatters;

use App\Models\Event;

class FireFormatter
{
    public static function format(Event $event): string
    {
        if ($event->frequency) {
            return ucwords($event->frequency->label());
        }

        return 'Monthly';
    }
}
