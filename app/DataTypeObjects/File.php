<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class File extends Data
{
    public string $url;

    public string $filename;

    public string $basename;

    public string $extension;

    public string $size;

    public string $mime_type;

    public int $size_bytes;
}
