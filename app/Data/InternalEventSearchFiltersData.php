<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\Internal\EventVisibilityFilter;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class InternalEventSearchFiltersData extends Data
{
    public function __construct(
        public ?float $lat,
        public ?float $lng,
        public ?float $radius,
        /** @var array<int> */
        public array $event_type_ids,
        /** @var array<string> */
        public array $fields,
        public string $place_name,
        public bool $active_only,
        public bool $include_deleted,
        public ?string $created_from,
        public ?string $created_to,
        public EventVisibilityFilter $privacy,
    ) {}

    /**
     * @param  array<int, string>  $fields
     */
    public static function fromCriteria(InternalEventSearchCriteriaData $criteria, array $fields, string $placeName): self
    {
        return new self(
            lat: $criteria->lat,
            lng: $criteria->lng,
            radius: $criteria->radius,
            event_type_ids: $criteria->eventTypeIds,
            fields: $fields,
            place_name: $placeName,
            active_only: $criteria->activeOnly,
            include_deleted: $criteria->includeDeleted,
            created_from: $criteria->createdFrom,
            created_to: $criteria->createdTo,
            privacy: $criteria->privacy,
        );
    }
}
