<?php

namespace App\Data\EventType;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * One row of the `action_buttons` grid on the event type blueprint.
 * `button_entry` holds the id of a linked Statamic entry when one is chosen instead of a URL.
 */
#[TypeScript]
class ActionButtonData extends Data
{
    public function __construct(
        public string $id,
        public ?string $button_label = null,
        public ?string $button_url = null,
        public ?string $button_entry = null,
        public bool $open_in_new_tab = false,
    ) {}
}
