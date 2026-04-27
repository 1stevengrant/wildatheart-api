<?php

namespace App\DataTransferObjects;

readonly class CreateEventDataTransferObject
{
    public function __construct(
        public string $name,
        public ?string $streetAddress,
        public ?string $state,
        public ?string $city,
        public ?string $zip,
        public string $country,
        public float $latitude,
        public float $longitude,
        public ?string $region,
        public ?string $district,
        public ?string $locality,
        public ?string $neighborhood,
        public ?string $frequency = null,
        public ?string $startDate = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            streetAddress: $data['street_address'] ?? null,
            state: $data['state'] ?? null,
            city: $data['city'] ?? null,
            zip: $data['zip'] ?? null,
            country: $data['country'] ?? '',
            latitude: $data['latitude'],
            longitude: $data['longitude'],
            region: $data['region'] ?? null,
            district: $data['district'] ?? null,
            locality: $data['locality'] ?? null,
            neighborhood: $data['neighborhood'] ?? null,
            frequency: $data['frequency'] ?? null,
            startDate: $data['start_date'] ?? null,
        );
    }
}
