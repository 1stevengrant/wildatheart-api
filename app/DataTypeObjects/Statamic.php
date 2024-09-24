<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Statamic\Entries\Entry as StatamicEntry;

#[TypeScript]
class Entry extends StatamicEntry
{
    public string $test_prop;
}

#[TypeScript]
class TestEntry
{
    public string $test_prop;
}
