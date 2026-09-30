<?php

namespace App\Data\V1;

use App\Models\EventType;
use App\Enums\EventSexEnum;
use Statamic\Facades\Entry;
use Spatie\LaravelData\Data;
use App\Data\EventType\FaqData;
use App\Data\EventType\KeyDetailData;
use App\Services\EventResponseBuilder;
use App\Support\EventTypeDateSelector;
use App\Data\EventType\ScheduleBlockData;
use App\Data\Concerns\CollectsNullableData;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use App\DataTypeObjects\API\V1\Content\Events\EventsShowResponse;

#[TypeScript]
class EventTypeData extends Data
{
    use CollectsNullableData;

    public function __construct(
        public int $id,
        public string $name,
        public string $camel_case_name,
        public string $slug,
        public EventSexEnum $sex,
        public string $image,
        public string $group_type,
        public int $order,
        public ?string $description,
        /** @var KeyDetailData[]|null */
        public ?array $key_details,
        public ?string $card_teaser_text,
        /** @var FaqData[]|null */
        public ?array $event_faqs,
        /** @var ScheduleBlockData[]|null */
        public ?array $event_schedule,
        public ?string $registration_before_your_event_text,
        public bool $allow_materials_order,
        public ?string $number_of_items_in_material_order_label,
        public ?string $type,
        public string $date_selector_kind,
        public ?int $fixed_duration_days,
        public bool $requires_frequency,
        public int $listings_count,
        public int $countries_count,
        public ?string $summary,
        public ?string $summary_link,
        #[TypeScriptType('\\' . EventsShowResponse::class . '|null')]
        public ?array $landing_page,
        public EventTypePageData $page,
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
            description: $eventType->page_description_text,
            key_details: self::collectOrNull(KeyDetailData::class, $eventType->key_details),
            card_teaser_text: $eventType->card_teaser_text,
            event_faqs: self::collectOrNull(FaqData::class, $eventType->event_faqs),
            event_schedule: self::collectOrNull(ScheduleBlockData::class, $eventType->event_schedule),
            registration_before_your_event_text: $eventType->registration_before_your_event_text,
            allow_materials_order: $eventType->allow_materials_order ?? false,
            number_of_items_in_material_order_label: $eventType->number_of_items_in_material_order_label,
            type: $eventType->type?->value,
            date_selector_kind: EventTypeDateSelector::kind($eventType->group_type),
            fixed_duration_days: EventTypeDateSelector::fixedDurationDays($eventType->group_type, $eventType->name),
            requires_frequency: EventTypeDateSelector::requiresFrequency($eventType->group_type),
            listings_count: $eventType->listings_count ?? 0,
            countries_count: $eventType->countries_count ?? 0,
            summary: $eventType->summary,
            summary_link: $eventType->summary_link,
            landing_page: self::landingPage($eventType),
            page: EventTypePageData::fromEventType($eventType),
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
