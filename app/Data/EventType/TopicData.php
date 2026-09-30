<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A term from the `topics` taxonomy, as picked in `page_topics` on the event type blueprint.
 */
#[TypeScript]
class TopicData extends Data
{
    public function __construct(
        public string $slug,
        public string $title,
    ) {}
}
