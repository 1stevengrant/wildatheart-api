<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ParentData extends Data
{
    public string $id;
    public string $title;
    public string $url;
    public string $permalink;
    public string $api_url;
}
