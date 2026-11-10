<?php

namespace App\Enums\Event;

/**
 * Where the staging Fire schedule tool moves a Fire's last date, counted in days from
 * the Fire's local today. Dates only move in whole weeks so the weekday never changes,
 * which means the last date lands within three days of the target. Every target sits at
 * least three days inside its window, so the result always triggers the same rule.
 */
enum ScheduleTestPreset: string
{
    case Quiet = 'quiet';
    case TwoWeekReminder = 'two_week_reminder';
    case FinalWeek = 'final_week';
    case Lapsed = 'lapsed';

    public function targetDays(): int
    {
        return match ($this) {
            self::Quiet => 21,
            self::TwoWeekReminder => 11,
            self::FinalWeek => 4,
            self::Lapsed => -4,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Quiet => '18 to 24 days away',
            self::TwoWeekReminder => '8 to 14 days away',
            self::FinalWeek => '1 to 7 days away',
            self::Lapsed => '1 to 7 days ago',
        };
    }

    public function expectation(): string
    {
        return match ($this) {
            self::Quiet => 'Nothing happens for any Fire.',
            self::TwoWeekReminder => 'Custom: the two week reminder. Weekly and Monthly: nothing.',
            self::FinalWeek => 'Custom: the final reminder. Weekly and Monthly: four more dates and the dates added email.',
            self::Lapsed => 'Custom: unpublished and the unpublished email. Weekly and Monthly: catch up from today and the dates added email.',
        };
    }
}
