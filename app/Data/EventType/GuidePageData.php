<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class GuidePageData extends Data
{
    public function __construct(
        public readonly string $page_title,
        /** @var array<ContentBlockData> */
        public readonly array $page_content,
    ) {}
}
