<?php

namespace App\DataTransferObjects;

use App\Models\EventType;
use App\Data\FireEventData;
use App\Support\EventDateHelper;
use Illuminate\Foundation\Http\FormRequest;

readonly class EventCreationData
{
    public function __construct(
        public ?string $name,
        public float $latitude,
        public float $longitude,
        public ?string $streetAddress = null,
        public ?string $city = null,
        public ?string $zip = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $region = null,
        public ?string $district = null,
        public ?string $locality = null,
        public ?string $neighborhood = null,
        public ?bool $isPrivate = null,
        public ?string $startDate = null,
        public ?string $endDate = null,
        public ?string $frequency = null,
        public ?string $materialsQuantity = null,
    ) {}

    public static function fromRequest(FormRequest $request, EventType $eventType): self
    {
        $isPrivate = $request->validated('is_private');

        return new self(
            name: $request->validated('name'),
            latitude: (float) $request->validated('latitude'),
            longitude: (float) $request->validated('longitude'),
            streetAddress: $request->validated('street_address'),
            city: $request->validated('city'),
            zip: $request->validated('zip'),
            state: $request->validated('state'),
            country: $request->validated('country'),
            region: $request->validated('region'),
            district: $request->validated('district'),
            locality: $request->validated('locality'),
            neighborhood: $request->validated('neighborhood'),
            isPrivate: $isPrivate === null ? null : (bool) $isPrivate,
            startDate: $request->validated('start_date'),
            endDate: EventDateHelper::endDateForRetreat(
                startDate: $request->validated('start_date'),
                endDate: $request->validated('end_date'),
                groupType: $eventType->group_type,
                eventTypeName: $eventType->name,
            ),
            frequency: $request->validated('frequency'),
            materialsQuantity: $request->validated('number_of_items_in_material_order'),
        );
    }

    public static function fromFireData(FireEventData $data): self
    {
        return new self(
            name: $data->name,
            latitude: (float) $data->latitude,
            longitude: (float) $data->longitude,
            streetAddress: $data->street_address,
            city: $data->city,
            zip: $data->zip,
            state: $data->state,
            country: $data->country,
            region: $data->region,
            district: $data->district,
            locality: $data->locality,
            neighborhood: $data->neighborhood,
            isPrivate: $data->is_private,
            frequency: $data->frequency,
        );
    }
}
