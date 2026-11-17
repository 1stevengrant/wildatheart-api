<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Enums\Internal\EventVisibilityFilter;

class InternalEventSearchCriteriaData extends Data
{
    /**
     * @param  array<int, int>  $eventTypeIds
     */
    public function __construct(
        public readonly ?float $lat,
        public readonly ?float $lng,
        public readonly ?float $radius,
        public readonly array $eventTypeIds,
        public readonly bool $activeOnly,
        public readonly bool $includeDeleted,
        public readonly ?string $createdFrom,
        public readonly ?string $createdTo,
        public readonly EventVisibilityFilter $privacy,
    ) {}

    public function hasCoordinates(): bool
    {
        return $this->lat !== null
            && $this->lng !== null
            && $this->radius !== null;
    }

    public function pageCacheKey(int $page): string
    {
        $params = [
            'lat' => $this->lat,
            'lng' => $this->lng,
            'radius' => $this->radius,
            'event_type_ids' => $this->eventTypeIds,
            'active_only' => $this->activeOnly,
            'include_deleted' => $this->includeDeleted,
            'created_from' => $this->createdFrom,
            'created_to' => $this->createdTo,
            'privacy' => $this->privacy->value,
            'page' => $page,
        ];

        return 'event_search:' . hash('xxh128', serialize($params));
    }
}
