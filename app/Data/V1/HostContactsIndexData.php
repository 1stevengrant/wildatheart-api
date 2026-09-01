<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class HostContactsIndexData extends Data
{
    public function __construct(
        public ?HostContactsFilterData $filter = null,
        public ?int $page = null,
        public ?int $per_page = null,
        public ?string $sort = null,
    ) {}
}
