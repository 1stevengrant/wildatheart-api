<?php

namespace App\DataTypeObjects\Statamic\Blueprints;

use Spatie\LaravelData\Data;
use App\DataTypeObjects\Blueprint;
use App\DataTypeObjects\ImageData;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CustomSeries extends Data
{
    public function __construct()
    {
        $this->blueprint = Blueprint::from(['title' => 'Custom Series', 'handle' => 'custom_series']);
    }

    public string $id;

    /** @var array<SlideBlock|GenericBlock> */
    public ?array $blocks;

    public ?ImageData $images;

    public string $locale = 'default';

    /** @var \App\DataTypeObjects\ParentData[] */
    public ?array $parent = null;

    public bool $private;

    public bool $published;

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
