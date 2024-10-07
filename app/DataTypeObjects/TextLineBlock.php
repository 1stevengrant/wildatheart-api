<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class TextLineBlock extends Data
{
    public string $type = 'text_line';
    public string $text;
    public StatamicValue $text_style;
    public string $id;
}
