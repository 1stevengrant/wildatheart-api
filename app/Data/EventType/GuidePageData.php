<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One `page` set of the `event_guide_pages` replicator on the event type blueprint.
 */
#[TypeScript]
class GuidePageData extends Data
{
    public function __construct(
        public string $id,
        public string $type = 'page',
        public bool $enabled = true,
        public ?string $page_title = null,
        /** @var ContentBlockData[] */
        public array $page_content = [],
    ) {}
}
