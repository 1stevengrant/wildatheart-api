<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One row of the `event_faqs` grid on the event type blueprint.
 */
#[TypeScript]
class FaqData extends Data
{
    public function __construct(
        public string $id,
        public string $question,
        public string $answer,
    ) {}
}
