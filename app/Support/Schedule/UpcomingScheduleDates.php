<?php

namespace App\Support\Schedule;

use App\Models\Event;
use App\Models\EventScheduleDate;
use Illuminate\Support\Collection;

class UpcomingScheduleDates
{
    /**
     * Dates on or after the Fire's local today, in date order. Today's date stays
     * in the list after it has ended so hosts still see it until midnight.
     *
     * @return Collection<int, EventScheduleDate>
     */
    public static function forEvent(Event $event): Collection
    {
        $schedule = $event->schedule;

        if (! $schedule) {
            return collect();
        }

        $today = $schedule->localToday()->toDateString();

        return $event->recentScheduleDates
            ->filter(fn (EventScheduleDate $date) => $date->date->toDateString() >= $today)
            ->sortBy('date')
            ->values();
    }

    /**
     * Dates that have not ended yet, for public listings.
     *
     * @return Collection<int, EventScheduleDate>
     */
    public static function notEnded(Event $event): Collection
    {
        return self::forEvent($event)
            ->reject(fn (EventScheduleDate $date) => $date->hasEnded())
            ->values();
    }
}
