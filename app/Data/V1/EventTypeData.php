<?php

namespace App\Data\V1;

use App\Models\EventType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventTypeData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $camel_case_name,
        public string $sex,
        public string $image,
        public string $group_type,
        public ?string $type,
        public int $listings_count,
        public int $countries_count,
        public ?string $summary,
        public ?string $summary_link,
    ) {}

    public static function fromEventType(EventType $eventType): self
    {
        return new self(
            id: $eventType->id,
            name: $eventType->name,
            camel_case_name: $eventType->camel_case_name,
            sex: $eventType->sex,
            image: $eventType->image,
            group_type: $eventType->group_type,
            type: $eventType->type?->value,
            listings_count: $eventType->listings_count ?? 0,
            countries_count: $eventType->countries_count ?? 0,
            summary: $eventType->summary,
            summary_link: $eventType->summary_link,
        );
    }
}
