<?php

namespace App\DataTypeObjects;

use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class PlayableFile extends File
{
    public string $playtime;
    public int $duration_seconds;
}
