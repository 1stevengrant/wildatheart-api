<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class RedirectData extends Data
{
    public function __construct(
        public string $source,
        public string $destination,
        public ?string $status_code = null,
        public ?int $count = null,
        public ?string $system_origin = null
    ) {}
}
