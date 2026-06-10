<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ScheduleBlockData extends Data
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $id,
        public readonly ?bool $enabled,
        public readonly ?string $text_block,
        public readonly ?string $video_url,
    ) {}
}
