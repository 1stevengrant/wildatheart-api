<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class MediaItemData extends Data
{
    public string $id;

    /** @var array<SlideBlock|GenericBlock> */
    public ?array $blocks;

    public ?ImageData $images;

    public string $locale = 'default';

    /** @var \App\DataTypeObjects\ParentData[] */
    public ?array $parent = null;

    public bool $private = false;

    public bool $published = true;

    public ?string $seo_description = null;

    public ?array $seo_image = null;

    public ?string $seo_title = null;

    public string $slug;

    public string $status = 'published';

    public string $title;

    public string $uri;

    public string $url;

    public Blueprint $blueprint;
}
