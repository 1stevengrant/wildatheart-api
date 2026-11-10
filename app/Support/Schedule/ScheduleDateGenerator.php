<?php

namespace App\Support\Schedule;

use Carbon\CarbonImmutable;
use InvalidArgumentException;
use App\Enums\Event\FrequencyEnum;
use Illuminate\Support\Collection;

class ScheduleDateGenerator
{
    public const int RENEWAL_COUNT = 4;

    private const array ORDINALS = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th'];

    /**
     * Dates that follow the anchor's pattern and fall strictly after $after.
     *
     * Monthly keeps the anchor's weekday position, so the 2nd Tuesday stays the 2nd Tuesday.
     * An anchor in the 5th week becomes the last weekday of the month, because not every
     * month has a 5th one.
     *
     * @return Collection<int, CarbonImmutable>
     */
    public static function datesAfter(
        FrequencyEnum $frequency,
        CarbonImmutable $anchor,
        CarbonImmutable $after,
        int $count = self::RENEWAL_COUNT,
    ): Collection {
        $anchor = CarbonImmutable::parse($anchor->toDateString());
        $after = CarbonImmutable::parse($after->toDateString());

        return match ($frequency) {
            FrequencyEnum::WEEKLY => self::weeklyDatesAfter($anchor, $after, $count),
            FrequencyEnum::MONTHLY => self::monthlyDatesAfter($anchor, $after, $count),
            default => throw new InvalidArgumentException("Frequency {$frequency->value} does not generate dates."),
        };
    }

    public static function patternLabel(FrequencyEnum $frequency, CarbonImmutable $anchor): ?string
    {
        $weekday = $anchor->englishDayOfWeek;

        return match ($frequency) {
            FrequencyEnum::WEEKLY => "Every {$weekday}",
            FrequencyEnum::MONTHLY => self::isLastWeekdayPosition($anchor)
                ? "Last {$weekday} of the month"
                : self::ORDINALS[self::weekOfMonth($anchor)] . " {$weekday} of the month",
            default => null,
        };
    }

    /**
     * @return Collection<int, CarbonImmutable>
     */
    private static function weeklyDatesAfter(CarbonImmutable $anchor, CarbonImmutable $after, int $count): Collection
    {
        $first = $anchor;

        if ($first->lessThanOrEqualTo($after)) {
            $weeksToSkip = intdiv((int) $anchor->diffInDays($after), 7) + 1;
            $first = $anchor->addWeeks($weeksToSkip);
        }

        return collect(range(0, $count - 1))
            ->map(fn (int $index) => $first->addWeeks($index));
    }

    /**
     * @return Collection<int, CarbonImmutable>
     */
    private static function monthlyDatesAfter(CarbonImmutable $anchor, CarbonImmutable $after, int $count): Collection
    {
        $isLast = self::isLastWeekdayPosition($anchor);
        $position = self::weekOfMonth($anchor);
        $month = $anchor->startOfMonth();
        $dates = collect();

        while ($dates->count() < $count) {
            $date = $isLast
                ? self::lastWeekdayOfMonth($month, $anchor->dayOfWeek)
                : self::nthWeekdayOfMonth($month, $anchor->dayOfWeek, $position);

            if ($date->greaterThan($after) && $date->greaterThanOrEqualTo($anchor)) {
                $dates->push($date);
            }

            $month = $month->addMonthNoOverflow();
        }

        return $dates;
    }

    private static function weekOfMonth(CarbonImmutable $date): int
    {
        return intdiv($date->day - 1, 7) + 1;
    }

    private static function isLastWeekdayPosition(CarbonImmutable $date): bool
    {
        return self::weekOfMonth($date) === 5;
    }

    private static function nthWeekdayOfMonth(CarbonImmutable $month, int $dayOfWeek, int $position): CarbonImmutable
    {
        $first = $month->startOfMonth();
        $offset = ($dayOfWeek - $first->dayOfWeek + 7) % 7;

        return $first->addDays($offset + ($position - 1) * 7);
    }

    private static function lastWeekdayOfMonth(CarbonImmutable $month, int $dayOfWeek): CarbonImmutable
    {
        $last = $month->endOfMonth()->startOfDay();
        $offset = ($last->dayOfWeek - $dayOfWeek + 7) % 7;

        return $last->subDays($offset);
    }
}
