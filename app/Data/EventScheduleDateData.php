<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Models\EventScheduleDate;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventScheduleDateData extends Data
{
    public function __construct(
        public string $date,
        public string $start_time,
        public string $end_time,
        public string $starts_at,
        public string $ends_at,
        public bool $has_custom_times,
        public bool $is_past,
    ) {}

    public static function fromModel(EventScheduleDate $date, string $timezone): self
    {
        return new self(
            date: $date->date->toDateString(),
            start_time: $date->localStartsAt($timezone)->format('H:i'),
            end_time: $date->localEndsAt($timezone)->format('H:i'),
            starts_at: $date->starts_at->toIso8601ZuluString(),
            ends_at: $date->ends_at->toIso8601ZuluString(),
            has_custom_times: $date->hasCustomTimes(),
            is_past: $date->hasEnded(),
        );
    }
}
