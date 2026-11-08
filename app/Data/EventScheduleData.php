<?php

namespace App\Data;

use App\Models\Event;
use Spatie\LaravelData\Data;
use App\Enums\Event\FrequencyEnum;
use App\Support\Schedule\UpcomingScheduleDates;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class EventScheduleData extends Data
{
    public function __construct(
        #[LiteralTypeScriptType("'weekly' | 'monthly' | 'varies'")]
        public FrequencyEnum $frequency,
        public string $timezone,
        public string $default_start_time,
        public string $default_end_time,
        public ?string $anchor_date,
        public ?string $pattern_label,
        public bool $auto_renews,
        public ?string $last_date,
        public bool $needs_more_dates,
        /** @var array<int, EventScheduleDateData> */
        public array $dates,
    ) {}

    public static function fromEvent(Event $event): ?self
    {
        $schedule = $event->schedule;

        if (! $schedule) {
            return null;
        }

        $upcoming = UpcomingScheduleDates::forEvent($event);
        $lastDate = $upcoming->last()?->date;

        return new self(
            frequency: $schedule->frequency,
            timezone: $schedule->timezone,
            default_start_time: $schedule->defaultStartTime(),
            default_end_time: $schedule->defaultEndTime(),
            anchor_date: $schedule->anchor_date?->toDateString(),
            pattern_label: $schedule->patternLabel(),
            auto_renews: $schedule->autoRenews(),
            last_date: $lastDate?->toDateString(),
            needs_more_dates: ! $schedule->autoRenews()
                && ($lastDate === null || $lastDate->lessThanOrEqualTo($schedule->localToday()->addDays(14))),
            dates: $upcoming
                ->map(fn ($date) => EventScheduleDateData::fromModel($date, $schedule->timezone))
                ->values()
                ->all(),
        );
    }
}
