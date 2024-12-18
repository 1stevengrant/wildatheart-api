<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Statamic\Fields\Blueprint;
use Statamic\Entries\Collection;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ContentPreview extends Data
{
    public $id = null;

    public ?string $title = null;

    public ?string $description = null;

    public ?string $uri = null;

    public ?string $featured_image_src = null;

    public ?string $published_at = null;

    public ?Collection $collection = null;

    public ?Blueprint $blueprint = null;

    public ?array $related_content = null;
}
