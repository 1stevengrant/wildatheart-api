<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ContentBlockData extends Data
{
    public function __construct(
        public readonly string $type,
        public readonly bool $enabled,
        public readonly ?string $body,
        public readonly ?string $video,
        public readonly ?string $image,
        public readonly ?string $accordion,
    ) {}
}
