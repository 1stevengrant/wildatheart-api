<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventScheduleDateInputData extends Data
{
    public function __construct(
        public string $date,
        public ?string $start_time = null,
        public ?string $end_time = null,
    ) {}
}
