<?php

namespace App\Actions\Event;

use App\Models\Event;
use Carbon\CarbonImmutable;
use App\Models\EventSchedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\EventScheduleNotification;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Enums\Event\ScheduleNotificationKind;
use App\Mail\Fire\Schedule\FireScheduleMailable;
use App\Mail\Fire\Schedule\FireScheduleLastDateMailable;
use App\Mail\Fire\Schedule\FireScheduleDatesAddedMailable;
use App\Mail\Fire\Schedule\FireScheduleUnpublishedMailable;
use App\Mail\Fire\Schedule\FireScheduleAddMoreDatesMailable;

/**
 * The daily pass over one published, scheduled Fire. Every check compares against the
 * Fire's local today with <=, so a missed run catches up the next day. Each email is
 * recorded once per last date, so a retry or a second run sends nothing new; a host
 * who adds dates changes the last date and starts a fresh cycle.
 */
class ProcessEventSchedule
{
    use AsAction;

    public const int RENEW_WITHIN_DAYS = 7;

    public const int ADD_MORE_WITHIN_DAYS = 14;

    public const int LAST_DATE_WITHIN_DAYS = 7;

    public function handle(Event $event): ?ScheduleNotificationKind
    {
        $event->loadMissing(['schedule', 'user', 'managers', 'eventType']);
        $schedule = $event->schedule;

        if (! $schedule || ! $event->isApproved()) {
            return null;
        }

        $today = $schedule->localToday();
        $lastDate = $schedule->dates()->max('date');

        if ($schedule->autoRenews()) {
            return $this->renew($event, $schedule, $today, $lastDate);
        }

        if ($lastDate === null) {
            return null;
        }

        return $this->remindOrUnpublish($event, CarbonImmutable::parse($lastDate), $today);
    }

    private function renew(Event $event, EventSchedule $schedule, CarbonImmutable $today, ?string $lastDate): ?ScheduleNotificationKind
    {
        $last = $lastDate ? CarbonImmutable::parse($lastDate) : null;

        if ($last && $last->greaterThan($today->addDays(self::RENEW_WITHIN_DAYS))) {
            return null;
        }

        $marker = $last ?? $today;
        $newDates = ExtendEventSchedule::make()->nextDates($schedule);

        if ($newDates->isEmpty()) {
            return null;
        }

        $mailable = new FireScheduleDatesAddedMailable(
            $event,
            $newDates->last()->toDateString(),
            $this->formatDates($schedule, $newDates),
        );

        $recorded = DB::transaction(function () use ($event, $schedule, $marker) {
            if (! $this->record($event, ScheduleNotificationKind::DatesAdded, $marker)) {
                return false;
            }

            ExtendEventSchedule::run($schedule);

            return true;
        });

        if (! $recorded) {
            return null;
        }

        SendFireHostMail::run($event, $mailable);

        return ScheduleNotificationKind::DatesAdded;
    }

    private function remindOrUnpublish(Event $event, CarbonImmutable $lastDate, CarbonImmutable $today): ?ScheduleNotificationKind
    {
        if ($lastDate->lessThan($today)) {
            return $this->unpublish($event, $lastDate);
        }

        $kind = match (true) {
            $lastDate->lessThanOrEqualTo($today->addDays(self::LAST_DATE_WITHIN_DAYS)) => ScheduleNotificationKind::LastDate,
            $lastDate->lessThanOrEqualTo($today->addDays(self::ADD_MORE_WITHIN_DAYS)) => ScheduleNotificationKind::AddMoreDates,
            default => null,
        };

        if (! $kind) {
            return null;
        }

        $mailable = $kind === ScheduleNotificationKind::LastDate
            ? new FireScheduleLastDateMailable($event, $lastDate->toDateString())
            : new FireScheduleAddMoreDatesMailable($event, $lastDate->toDateString());

        return $this->sendOnce($event, $kind, $lastDate, $mailable);
    }

    private function unpublish(Event $event, CarbonImmutable $lastDate): ?ScheduleNotificationKind
    {
        $mailable = new FireScheduleUnpublishedMailable($event, $lastDate->toDateString());

        $recorded = DB::transaction(function () use ($event, $lastDate) {
            if (! $this->record($event, ScheduleNotificationKind::Unpublished, $lastDate)) {
                return false;
            }

            $event->update([
                'approved_at' => null,
                'auto_unpublished' => true,
                'auto_unpublished_at' => now(),
            ]);

            activity('fire')
                ->performedOn($event)
                ->log('Auto-unpublished after its last scheduled date');

            return true;
        });

        if (! $recorded) {
            return null;
        }

        SendFireHostMail::run($event, $mailable);

        return ScheduleNotificationKind::Unpublished;
    }

    private function sendOnce(Event $event, ScheduleNotificationKind $kind, CarbonImmutable $lastDate, FireScheduleMailable $mailable): ?ScheduleNotificationKind
    {
        if (! $this->record($event, $kind, $lastDate)) {
            return null;
        }

        SendFireHostMail::run($event, $mailable);

        return $kind;
    }

    private function record(Event $event, ScheduleNotificationKind $kind, CarbonImmutable $lastDate): bool
    {
        $now = now();

        return EventScheduleNotification::insertOrIgnore([
            'event_id' => $event->id,
            'kind' => $kind->value,
            'last_date' => $lastDate->toDateString(),
            'created_at' => $now,
            'updated_at' => $now,
        ]) === 1;
    }

    /**
     * @param  Collection<int, CarbonImmutable>  $dates
     * @return array<int, string>
     */
    private function formatDates(EventSchedule $schedule, Collection $dates): array
    {
        return $dates->map(function (CarbonImmutable $date) use ($schedule) {
            $startsAt = CarbonImmutable::parse("{$date->toDateString()} {$schedule->defaultStartTime()}", $schedule->timezone);
            $endsAt = CarbonImmutable::parse("{$date->toDateString()} {$schedule->defaultEndTime()}", $schedule->timezone);

            return "{$startsAt->format('l, F j, g:i A')} to {$endsAt->format('g:i A T')}";
        })->all();
    }
}
