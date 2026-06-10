<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class KeyDetailData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $icon,
        public readonly string $headline,
        public readonly string $description,
    ) {}
}
