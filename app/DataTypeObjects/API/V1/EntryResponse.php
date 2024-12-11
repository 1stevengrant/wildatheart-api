<?php

namespace App\DataTypeObjects\API\V1;

use Spatie\LaravelData\Data;
use App\DataTypeObjects\Blueprint;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EntryResponse extends Data
{
    public string $id;

    public string $title;

    public bool $private = false;

    public bool $published = true;

    public string $status = 'published';

    public string $slug;

    public ?string $uri;

    public ?string $url;

    public Blueprint $blueprint;

    public string $locale = 'default';
}
