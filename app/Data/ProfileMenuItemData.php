<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ProfileMenuItemData extends Data
{
    public function __construct(
        public string $label,
        public string $href,
        public ?string $iconName = null,
    ) {}
}
