<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class FeaturedContentItemData extends Data
{
    public function __construct(
        public ?string $id,
        public ?string $item_text,
        public ?string $item_url,
        public ?string $item_image,
        /** @var array<string> */
        public array $show_in_regions,
        public ?string $relates_to_event_id,
        public ?float $event_latitude,
        public ?float $event_longitude,
    ) {}
}