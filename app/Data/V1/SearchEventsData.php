<?php

namespace App\Data\V1;

use App\Enums\EventSexEnum;
use Spatie\LaravelData\Data;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class SearchEventsData extends Data
{
    public function __construct(
        public ?float $sw_latitude,
        public ?float $sw_longitude,
        public ?float $ne_latitude,
        public ?float $ne_longitude,
        public ?float $latitude,
        public ?float $longitude,
        public ?float $radius,
        public ?float $min_radius,
        public ?string $sex,
        public ?string $type_group,
        public ?string $types,
        public ?string $start_date,
        public ?string $end_date,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        $payload = $context->payload;
        $hasCenterPoint = ! empty($payload['latitude']) && ! empty($payload['longitude']);
        $boundsRequired = $hasCenterPoint ? 'nullable' : 'required';

        return [
            'sw_latitude' => [$boundsRequired, 'numeric', 'between:-90,90'],
            'sw_longitude' => [$boundsRequired, 'numeric'],
            'ne_latitude' => [$boundsRequired, 'numeric', 'between:-90,90'],
            'ne_longitude' => [$boundsRequired, 'numeric'],

            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:12500'],
            'min_radius' => ['nullable', 'numeric', 'min:0', 'max:12500'],

            'sex' => ['nullable', 'string', Rule::enum(EventSexEnum::class)],
            'type_group' => ['nullable', 'string'],
            'types' => ['nullable', 'string', 'regex:/^\d+(,\d+)*$/'],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public static function messages(): array
    {
        return [
            'sw_latitude.between' => 'The southwest latitude must be between -90 and 90 degrees.',
            'ne_latitude.between' => 'The northeast latitude must be between -90 and 90 degrees.',
            'latitude.between' => 'The latitude must be between -90 and 90 degrees.',
            'types.regex' => 'The types must be a comma-separated list of numbers.',
        ];
    }

    public function isCenterPointMode(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
