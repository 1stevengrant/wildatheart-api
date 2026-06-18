<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\Internal\EventVisibilityFilter;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class InternalEventSearchFiltersData extends Data
{
    public function __construct(
        public ?float $lat,
        public ?float $lng,
        public ?float $radius,
        /** @var array<int> */
        public array $event_type_ids,
        /** @var array<string> */
        public array $fields,
        public string $place_name,
        public bool $active_only,
        public bool $include_deleted,
        public ?string $created_from,
        public ?string $created_to,
        public EventVisibilityFilter $privacy,
    ) {}
}
