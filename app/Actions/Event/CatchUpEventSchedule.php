<?php

namespace App\Actions\Event;

use App\Models\Event;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Support\Schedule\UpcomingScheduleDates;

/**
 * Unpublished Weekly and Monthly Fires do not renew, so republishing one with no
 * upcoming dates adds the next four from today. No email: the host sees them.
 */
class CatchUpEventSchedule
{
    use AsAction;

    public function handle(Event $event): void
    {
        $schedule = $event->schedule()->first();

        if (! $schedule?->autoRenews()) {
            return;
        }

        $event->setRelation('schedule', $schedule)
            ->setRelation('recentScheduleDates', $event->recentScheduleDates()->get());

        if (UpcomingScheduleDates::notEnded($event)->isNotEmpty()) {
            return;
        }

        ExtendEventSchedule::run($schedule);
        $event->unsetRelation('recentScheduleDates');
    }
}
