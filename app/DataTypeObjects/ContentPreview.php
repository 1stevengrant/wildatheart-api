<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Statamic\Fields\Blueprint;
use Statamic\Entries\Collection;
use App\StatamicDTO\Shared\CollectionReference;
use App\DataTypeObjects\Blueprint as BlueprintData;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
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

    #[TypeScriptType('\\' . CollectionReference::class . '|null')]
    public ?Collection $collection = null;

    #[TypeScriptType('\\' . BlueprintData::class . '|null')]
    public ?Blueprint $blueprint = null;

    /** @var array<int, array<string, mixed>>|null Never populated by PreviewBuilderService; kept for the slide builder that reads it */
    #[LiteralTypeScriptType('Array<Record<string, unknown>> | null')]
    public ?array $related_content = null;
}
