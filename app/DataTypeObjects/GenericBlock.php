<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A generic block to fall back on while we're
 * still building out the rest of the blocks.
 */
#[TypeScript]
class GenericBlock extends Data
{
    public string $id;

    public string $title;

    public string $type;
}
