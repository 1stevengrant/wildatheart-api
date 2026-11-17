<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Illuminate\Validation\Rule;
use App\Enums\Event\FrequencyEnum;
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
                'max:255',
                new ShouldNotEndWithFire,
                new DoesntContainEmojis,
            ],
            'frequency' => ['required', 'string', Rule::enum(FrequencyEnum::class)],
            'is_private' => ['nullable', 'boolean'],
        ]);
    }
}
