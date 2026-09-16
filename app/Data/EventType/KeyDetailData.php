<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One row of the `key_details` grid on the event type blueprint.
 */
#[TypeScript]
class KeyDetailData extends Data
{
    public function __construct(
        public string $id,
        public ?string $icon = null,
        public ?string $headline = null,
        public ?string $description = null,
    ) {}
}
