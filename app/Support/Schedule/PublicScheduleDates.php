<?php

namespace App\Support\Schedule;

use App\Models\Event;
use App\Models\EventScheduleDate;
use App\Data\V1\NextEventDateData;

class PublicScheduleDates
{
    public const int UPCOMING_LIMIT = 8;

    public const array RELATIONS = ['schedule', 'recentScheduleDates'];

    public static function next(Event $event): ?NextEventDateData
    {
        return self::upcoming($event, 1)[0] ?? null;
    }

    /**
     * Returns null when the schedule relations were not eager loaded, so a caller that
     * forgets them gets the old behaviour rather than one query per event.
     *
     * @return array<int, NextEventDateData>|null
     */
    public static function upcoming(Event $event, int $limit = self::UPCOMING_LIMIT): ?array
    {
        if (! $event->relationLoaded('schedule') || ! $event->schedule) {
            return null;
        }

        $timezone = $event->schedule->timezone;

        return UpcomingScheduleDates::notEnded($event)
            ->take($limit)
            ->map(fn (EventScheduleDate $date) => NextEventDateData::fromModel($date, $timezone))
            ->all();
    }
}
