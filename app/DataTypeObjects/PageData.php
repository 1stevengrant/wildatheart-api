<?php

namespace App\DataTypeObjects;

use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PageData extends Data
{
    public string $id;
    public array $blocks;
    public ImageData $images;
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
}


class ParentData extends Data
{
    public string $id;
    public string $title;
    public string $url;
    public string $permalink;
    public string $api_url;
}
