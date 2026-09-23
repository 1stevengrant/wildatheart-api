<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * A bare text set without an id, as stored in legacy `hero_subtitle` fields.
 */
#[TypeScript]
class TextBlock extends Data
{
    public string $type = 'text';

    public string $text;
}
