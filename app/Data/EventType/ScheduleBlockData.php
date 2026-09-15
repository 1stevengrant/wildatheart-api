<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use App\Enums\EventScheduleBlockTypeEnum;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One set of the `event_schedule` replicator on the event type blueprint.
 * A `text` set carries `text_block`; a `video` set carries `embed_code` and `video_url`.
 */
#[TypeScript]
class ScheduleBlockData extends Data
{
    public function __construct(
        public string $id,
        public EventScheduleBlockTypeEnum $type,
        public bool $enabled = true,
        public ?string $text_block = null,
        public ?string $embed_code = null,
        public ?string $video_url = null,
    ) {}
}
