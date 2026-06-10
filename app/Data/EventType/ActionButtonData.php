<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ActionButtonData extends Data
{
    public function __construct(
        public readonly string $id,
        public readonly string $button_label,
        public readonly ?string $button_url,
        public readonly bool $open_in_new_tab,
    ) {}
}
