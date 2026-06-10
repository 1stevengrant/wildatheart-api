<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Statamic\Fields\Blueprint;
use Statamic\Entries\Collection;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class ContentPreview extends Data
{
    public ?string $id = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $uri = null;

    public ?string $featured_image_src = null;

    public ?string $published_at = null;

    #[LiteralTypeScriptType('Record<string, unknown> | null')]
    public ?Collection $collection = null;

    #[LiteralTypeScriptType('Record<string, unknown> | null')]
    public ?Blueprint $blueprint = null;

    #[LiteralTypeScriptType('Array<Record<string, unknown>> | null')]
    public ?array $related_content = null;
}
