<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use App\Models\EventScheduleDate;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A scheduled Fire date in the Fire's own timezone. Show it as is rather than
 * converting to the viewer's timezone.
 */
#[TypeScript]
class NextEventDateData extends Data
{
    public function __construct(
        public string $date,
        public string $startTime,
        public string $endTime,
        public string $startsAt,
        public string $endsAt,
        public string $timezone,
        public string $timezoneAbbreviation,
    ) {}

    public static function fromModel(EventScheduleDate $date, string $timezone): self
    {
        $startsAt = $date->localStartsAt($timezone);

        return new self(
            date: $date->date->toDateString(),
            startTime: $startsAt->format('H:i'),
            endTime: $date->localEndsAt($timezone)->format('H:i'),
            startsAt: $date->starts_at->toIso8601ZuluString(),
            endsAt: $date->ends_at->toIso8601ZuluString(),
            timezone: $timezone,
            timezoneAbbreviation: $startsAt->format('T'),
        );
    }
}
