<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ImageData extends Data
{
    public ?string $square = null;
    public ?string $wide = null;
    public ?string $banner = null;
    public ?string $vertical = null;
}
