<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventGuideStepsData extends Data
{
    public function __construct(
        public bool $welcome,
        public bool $before,
        public bool $schedule,
        public bool $faqs,
    ) {}
}
