<?php

namespace App\Data\V1;

use App\Models\EventType;
use Statamic\Facades\Entry;
use Spatie\LaravelData\Data;
use App\Services\EventResponseBuilder;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventTypeData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $camel_case_name,
        public string $slug,
        public string $sex,
        public string $image,
        public string $group_type,
        public int $order,
        public ?string $description,
        public ?array $key_details,
        public ?string $card_teaser_text,
        public ?array $event_faqs,
        public ?array $event_schedule,
        public ?string $registration_before_your_event_text,
        public bool $allow_materials_order,
        public ?string $number_of_items_in_material_order_label,
        public ?string $type,
        public int $listings_count,
        public int $countries_count,
        public ?string $summary,
        public ?string $summary_link,
        public ?array $landing_page,
    ) {}

    public static function fromEventType(EventType $eventType): self
    {
        return new self(
            id: $eventType->id,
            name: $eventType->name,
            camel_case_name: $eventType->camel_case_name,
            slug: $eventType->slug,
            sex: $eventType->sex,
            image: $eventType->image,
            group_type: $eventType->group_type,
            order: $eventType->order ?? 0,
            description: $eventType->description,
            key_details: $eventType->key_details,
            card_teaser_text: $eventType->card_teaser_text,
            event_faqs: $eventType->event_faqs,
            event_schedule: $eventType->event_schedule,
            registration_before_your_event_text: $eventType->registration_before_your_event_text,
            allow_materials_order: $eventType->allow_materials_order ?? false,
            number_of_items_in_material_order_label: $eventType->number_of_items_in_material_order_label,
            type: $eventType->type?->value,
            listings_count: $eventType->listings_count ?? 0,
            countries_count: $eventType->countries_count ?? 0,
            summary: $eventType->summary,
            summary_link: $eventType->summary_link,
            landing_page: self::landingPage($eventType),
        );
    }

    private static function landingPage(EventType $eventType): ?array
    {
        if (! $eventType->related_landing_page_id) {
            return null;
        }

        $landingPage = Entry::find($eventType->related_landing_page_id);

        if (! $landingPage) {
            return null;
        }

        return app(EventResponseBuilder::class)->build($landingPage);
    }
}
