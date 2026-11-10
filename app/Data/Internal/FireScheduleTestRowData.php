<?php

namespace App\Data\Internal;

use App\Models\Event;
use Spatie\LaravelData\Data;
use App\Models\EventScheduleDate;
use App\Models\EventScheduleNotification;
use App\Enums\Event\ScheduleNotificationKind;

class FireScheduleTestRowData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $eventType,
        public string $ownerEmail,
        /** @var array<int, string> */
        public array $coHostEmails,
        public bool $isPublished,
        public bool $wasUnpublishedBySchedule,
        public string $frequency,
        public ?string $pattern,
        public string $timezone,
        public string $localToday,
        public ?string $lastDate,
        public ?int $daysUntilLastDate,
        /** @var array<int, string> */
        public array $dates,
        /** @var array<int, string> */
        public array $sentEmails,
    ) {}

    public static function fromEvent(Event $event): self
    {
        $schedule = $event->schedule;
        $localToday = $schedule->localToday();
        $lastDate = $event->scheduleDates->max('date');

        return new self(
            id: $event->id,
            name: $event->name ?? 'Untitled Fire',
            eventType: $event->eventType->name,
            ownerEmail: $event->user->email,
            coHostEmails: $event->managers->pluck('email')->all(),
            isPublished: $event->isApproved(),
            wasUnpublishedBySchedule: $event->auto_unpublished && ! $event->isApproved(),
            frequency: $schedule->autoRenews() ? $schedule->frequency->label() : 'Custom',
            pattern: $schedule->patternLabel(),
            timezone: $schedule->timezone,
            localToday: $localToday->format('D j M Y'),
            lastDate: $lastDate?->format('D j M Y'),
            daysUntilLastDate: $lastDate ? (int) $localToday->diffInDays($lastDate, false) : null,
            dates: $event->scheduleDates
                ->map(fn (EventScheduleDate $date) => self::formatDate($event, $date))
                ->all(),
            sentEmails: $event->scheduleNotifications
                ->sortBy('created_at')
                ->map(fn (EventScheduleNotification $notification) => self::describeNotification($notification))
                ->values()
                ->all(),
        );
    }

    private static function describeNotification(EventScheduleNotification $notification): string
    {
        $email = match ($notification->kind) {
            ScheduleNotificationKind::DatesAdded => 'Dates added',
            ScheduleNotificationKind::AddMoreDates => 'Two week reminder',
            ScheduleNotificationKind::LastDate => 'Final reminder',
            ScheduleNotificationKind::Unpublished => 'Unpublished',
        };

        return "{$email} for last date {$notification->last_date->format('j M Y')}, sent {$notification->created_at->format('j M Y H:i')} UTC";
    }

    private static function formatDate(Event $event, EventScheduleDate $date): string
    {
        $timezone = $event->schedule->timezone;

        return "{$date->date->format('D j M Y')}, {$date->localStartsAt($timezone)->format('g:i A')} to {$date->localEndsAt($timezone)->format('g:i A T')}";
    }
}
