<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class RedirectData extends Data
{
    public function __construct(
        public string $source,
        public string $destination
    ) {}
}
