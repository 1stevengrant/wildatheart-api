<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class FaqData extends Data
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $question,
        public readonly string $answer,
    ) {}
}
