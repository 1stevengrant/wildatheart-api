<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ShowData extends Data
{
    public Show $data;
}

#[TypeScript]
class Show extends Data
{
    public string $id;

    public string $type;
}
