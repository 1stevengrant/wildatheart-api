<?php

namespace App\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use App\Enums\Event\FrequencyEnum;
use App\Support\Schedule\ScheduleDateGenerator;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class SchedulePreviewData extends Data
{
    public function __construct(
        /** @var string[] */
        public array $dates,
        public string $pattern_label,
    ) {}

    public static function forAnchor(FrequencyEnum $frequency, CarbonImmutable $anchor, int $count): self
    {
        return new self(
            dates: ScheduleDateGenerator::datesAfter($frequency, $anchor, $anchor->subDay(), $count)
                ->map(fn (CarbonImmutable $date) => $date->toDateString())
                ->all(),
            pattern_label: ScheduleDateGenerator::patternLabel($frequency, $anchor),
        );
    }
}
