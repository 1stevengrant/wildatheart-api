<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class HeroTitleBlock extends Data
{
    public string $type = 'hero_title';

    /** @var array<TextLineBlock> */
    public array $hero_title;
}
