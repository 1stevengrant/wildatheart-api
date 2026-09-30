<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One row of the `page_hero_title` grid on the event type blueprint.
 */
#[TypeScript]
class HeroTitleLineData extends Data
{
    public function __construct(
        public string $text,
        public ?string $text_style = null,
    ) {}
}
