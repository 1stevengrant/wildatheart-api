<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventFormData extends Data
{
    public function __construct(
        public string $name,
        public ?string $state = null,
        public ?string $city = null,
        public ?string $zip = null,
        public ?string $country = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $region = null,
        public ?string $district = null,
        public ?string $locality = null,
        public ?string $neighborhood = null,
        public ?string $street_address = null,
        public ?string $poi = null,
        public ?bool $is_private = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?string $number_of_items_in_material_order = null,
        public ?string $cadence = null,
    ) {}
}
