<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use App\Data\Concerns\HasLocationRules;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class UpdateEventData extends Data
{
    use HasLocationRules;

    public function __construct(
        public string $start_date,
        public ?string $end_date,
        public bool|Optional $is_private,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $country = null,
        public ?string $region = null,
        public ?string $district = null,
        public ?string $locality = null,
        public ?string $neighborhood = null,
        public ?string $street_address = null,
        public ?string $poi = null,
        public ?string $city = null,
        public ?string $zip = null,
        public ?string $state = null,
        public ?string $place = null,
    ) {}

    public static function rules(): array
    {
        return array_merge(self::locationRules(), [
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date'],
            'is_private' => ['nullable', 'boolean'],
        ]);
    }
}
