<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\Event\FrequencyEnum;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventFormData extends Data
{
    public function __construct(
        public string $name,
        public ?string $state,
        public ?string $city,
        public ?string $zip,
        public ?string $country,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $region,
        public ?string $district,
        public ?string $locality,
        public ?string $neighborhood,
        public ?string $street_address,
        public ?string $poi,
        public ?bool $is_private,
        public ?string $start_date,
        public ?string $end_date,
        public ?string $number_of_items_in_material_order,
        #[WithCast(EnumCast::class)]
        public ?FrequencyEnum $frequency,
        public ?int $day_count = null,
    ) {}
}
