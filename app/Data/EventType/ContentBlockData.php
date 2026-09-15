<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use App\Enums\EventGuideBlockTypeEnum;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One set of the event guide `page_content` and `accordion_box_content` replicators.
 * `body_set` and `text_set` carry `body`, `video_set` carries `video`,
 * `image_set` carries `image` and `accordion_set` carries `accordion`.
 */
#[TypeScript]
class ContentBlockData extends Data
{
    public function __construct(
        public string $id,
        public EventGuideBlockTypeEnum $type,
        public bool $enabled = true,
        public ?string $body = null,
        public ?string $video = null,
        public ?string $image = null,
        /** @var AccordionItemData[]|null */
        public ?array $accordion = null,
    ) {}
}
