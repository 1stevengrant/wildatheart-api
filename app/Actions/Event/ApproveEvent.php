<?php

namespace App\Actions\Event;

use App\Models\Event;
use App\Jobs\LogActivityJob;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Support\Schedule\UpcomingScheduleDates;

class ApproveEvent
{
    use AsAction;

    public function handle(
        Event $event
    ): void {
        $event->update([
            'approved_at' => now(),
        ]);

        $this->catchUpSchedule($event);

        LogActivityJob::dispatch(
            description: 'Event approved',
            performedOn: $event,
        );
    }

    /**
     * Unpublished Weekly and Monthly Fires do not renew, so republishing one with no
     * upcoming dates adds the next four from today. No email: the host sees them.
     */
    private function catchUpSchedule(Event $event): void
    {
        $schedule = $event->schedule()->first();

        if (! $schedule?->autoRenews()) {
            return;
        }

        $event->unsetRelation('recentScheduleDates')->setRelation('schedule', $schedule);

        if (UpcomingScheduleDates::notEnded($event)->isNotEmpty()) {
            return;
        }

        ExtendEventSchedule::run($schedule);
    }
}
