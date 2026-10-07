<?php

namespace App\Data;

use App\Models\EventType;
use Spatie\LaravelData\Data;
use App\Rules\DoesntContainEmojis;
use App\Rules\ShouldNotEndWithFire;
use App\Data\Concerns\HasLocationRules;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class FireEventData extends Data
{
    use HasLocationRules;

    public function __construct(
        public string $name,
        public string $frequency,
        public ?bool $is_private,
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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:' . EventType::MAX_FIRE_NAME_LENGTH,
                new ShouldNotEndWithFire,
                new DoesntContainEmojis,
            ],
            'frequency' => ['required', 'string'],
            'is_private' => ['nullable', 'boolean'],
        ]);
    }
}
