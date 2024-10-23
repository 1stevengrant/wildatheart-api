<?php

namespace App\DataTypeObjects\API\V1;

use App\DataTypeObjects\Blueprint;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ErrorResponse extends Data
{
    public string $error;
}
