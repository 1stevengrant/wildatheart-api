<?php

namespace App\Support\Schedule;

use App\Models\Event;

class ScheduleReadiness
{
    public const string MISSING_DATE_MESSAGE = 'Add at least one upcoming date before publishing.';

    /**
     * A Fire without a schedule is not judged here. Weekly and Monthly Fires can always
     * publish because publishing adds dates; Custom Fires need an upcoming date.
     */
    public static function canPublish(Event $event): bool
    {
        $event->loadMissing(['schedule', 'recentScheduleDates']);

        if (! $event->schedule) {
            return true;
        }

        if ($event->schedule->autoRenews()) {
            return true;
        }

        return UpcomingScheduleDates::notEnded($event)->isNotEmpty();
    }
}
