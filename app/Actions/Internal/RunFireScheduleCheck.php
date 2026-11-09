<?php

namespace App\Actions\Internal;

use App\Models\Event;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Actions\Event\ProcessEventSchedule;
use App\Enums\Event\ScheduleNotificationKind;

/**
 * Staging only. Runs the daily schedule check for one Fire, straight away, and says what
 * it did. Mail goes to the database mailer on the sync queue for this request, so every
 * email lands on the captured emails page and none leaves staging, whatever MAIL_MAILER
 * is set to.
 */
class RunFireScheduleCheck
{
    use AsAction;

    public function handle(Event $event): string
    {
        config(['mail.default' => 'database', 'queue.default' => 'sync']);

        $event->load(['schedule', 'user', 'managers', 'eventType']);
        $datesBefore = $event->scheduleDates()->count();

        $kind = ProcessEventSchedule::run($event);

        if (! $kind && ! $event->isApproved()) {
            return 'Nothing to do: the check skips unpublished Fires. Reset reminder state republishes a Fire the schedule unpublished.';
        }

        if (! $kind) {
            return 'Nothing to do: no rule applies to this Fire today, or its email for this last date was already sent.';
        }

        $recipients = $this->recipients($event);
        $addedDates = $event->scheduleDates()->count() - $datesBefore;

        return match ($kind) {
            ScheduleNotificationKind::DatesAdded => "Added {$addedDates} dates and sent the dates added email {$recipients}.",
            ScheduleNotificationKind::AddMoreDates => "Sent the two week reminder {$recipients}.",
            ScheduleNotificationKind::LastDate => "Sent the final reminder {$recipients}.",
            ScheduleNotificationKind::Unpublished => "Unpublished the Fire and sent the unpublished email {$recipients}.",
        };
    }

    private function recipients(Event $event): string
    {
        $coHosts = $event->managers->pluck('email')->all();

        if ($coHosts === []) {
            return "to {$event->user->email}";
        }

        return "to {$event->user->email}, CC " . Arr::join($coHosts, ', ', ' and ');
    }
}
