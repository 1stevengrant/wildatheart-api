<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class StatamicValue extends Data
{
    public string $value;

    public string $label;

    public string $key;
}
