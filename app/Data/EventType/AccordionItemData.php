<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One row of the `accordion` grid inside an event guide `accordion_set`.
 */
#[TypeScript]
class AccordionItemData extends Data
{
    public function __construct(
        public string $id,
        public ?string $accordion_box_title = null,
        /** @var ContentBlockData[] */
        public array $accordion_box_content = [],
    ) {}
}
