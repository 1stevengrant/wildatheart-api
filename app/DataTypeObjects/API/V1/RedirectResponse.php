<?php

namespace App\DataTypeObjects\API\V1;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class RedirectResponse extends Data
{
    public string $redirect;
    public string $code;
}
