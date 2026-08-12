<?php

namespace App\Data\V1;

use App\Enums\EventSexEnum;
use Spatie\LaravelData\Data;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class SearchEventsData extends Data
{
    public const DEFAULT_NEAREST = 20;

    public const MAX_NEAREST = 100;

    public const MAX_PER_PAGE = 200;

    public function __construct(
        public ?float $sw_latitude,
        public ?float $sw_longitude,
        public ?float $ne_latitude,
        public ?float $ne_longitude,
        public ?float $latitude,
        public ?float $longitude,
        public ?float $radius,
        public ?float $min_radius,
        public ?int $nearest,
        public ?int $per_page,
        public ?int $page,
        public ?string $sex,
        public ?string $type_group,
        public ?string $types,
        public ?string $start_date,
        public ?string $end_date,
    ) {}

    public static function rules(ValidationContext $context): array
    {
        $payload = $context->payload;

        // Deliberately not empty(): latitude 0 and longitude 0 are valid
        // coordinates, and a search near the equator or the prime meridian
        // should not fall back to requiring bounds.
        $hasCenterPoint = self::isPresent($payload, 'latitude') && self::isPresent($payload, 'longitude');
        $boundsRequired = $hasCenterPoint ? 'nullable' : 'required';
        $centerPointRequired = self::isPresent($payload, 'nearest') ? 'required' : 'nullable';

        return [
            'sw_latitude' => [$boundsRequired, 'numeric', 'between:-90,90'],
            'sw_longitude' => [$boundsRequired, 'numeric'],
            'ne_latitude' => [$boundsRequired, 'numeric', 'between:-90,90'],
            'ne_longitude' => [$boundsRequired, 'numeric'],

            'latitude' => [$centerPointRequired, 'numeric', 'between:-90,90'],
            'longitude' => [$centerPointRequired, 'numeric'],
            'radius' => ['nullable', 'numeric', 'min:1', 'max:12500'],
            'min_radius' => ['nullable', 'numeric', 'min:0', 'max:12500'],
            'nearest' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_NEAREST],

            'per_page' => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_PER_PAGE],
            'page' => ['nullable', 'integer', 'min:1'],

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
            'latitude.required' => 'The latitude is required when searching for the nearest events.',
            'longitude.required' => 'The longitude is required when searching for the nearest events.',
            'types.regex' => 'The types must be a comma-separated list of numbers.',
        ];
    }

    private static function isPresent(array $payload, string $key): bool
    {
        return isset($payload[$key]) && $payload[$key] !== '';
    }

    public function isCenterPointMode(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function isNearestMode(): bool
    {
        return $this->isCenterPointMode() && $this->nearest !== null;
    }

    public function nearestLimit(): int
    {
        return min($this->nearest ?? self::DEFAULT_NEAREST, self::MAX_NEAREST);
    }

    public function isPaginated(): bool
    {
        return $this->per_page !== null;
    }

    public function perPage(): int
    {
        return min($this->per_page ?? self::MAX_PER_PAGE, self::MAX_PER_PAGE);
    }

    public function currentPage(): int
    {
        return max($this->page ?? 1, 1);
    }
}
