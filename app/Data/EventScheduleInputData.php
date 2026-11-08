<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\Event\FrequencyEnum;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

/**
 * The `schedule` body sent on store, draft and publish. Validation lives in
 * ValidatesEventSchedule because strictness depends on the request.
 */
#[TypeScript]
class EventScheduleInputData extends Data
{
    public function __construct(
        #[LiteralTypeScriptType("'weekly' | 'monthly' | 'varies'")]
        public FrequencyEnum $frequency,
        public string $timezone,
        public string $default_start_time,
        public string $default_end_time,
        public ?string $anchor_date = null,
        /** @var array<int, EventScheduleDateInputData>|null */
        public ?array $dates = null,
    ) {}
}
