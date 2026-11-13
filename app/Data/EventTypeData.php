<?php

namespace App\Data;

use App\Models\EventType;
use App\Enums\EventSexEnum;
use App\Enums\EventTypeEnum;
use Spatie\LaravelData\Data;
use App\Data\EventType\FaqData;
use App\Data\EventType\GuidePageData;
use App\Data\EventType\KeyDetailData;
use App\Data\EventType\ActionButtonData;
use App\Data\EventType\ScheduleBlockData;
use App\Data\Concerns\CollectsNullableData;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class EventTypeData extends Data
{
    use CollectsNullableData;

    public function __construct(
        public int $id,
        public readonly bool $allow_materials_order,
        public readonly ?string $number_of_items_in_material_order_label,
        #[LiteralTypeScriptType("'fire' | 'retreat' | 'experience'")]
        public readonly string $group_type,
        public readonly ?EventTypeEnum $type,
        public readonly string $name,
        public readonly ?string $filter_name,
        public readonly int $order,
        public readonly ?string $free_gift_image,
        public readonly ?string $zapier_webhook_url,
        public readonly ?string $card_teaser_text,
        public readonly ?string $tags,
        public readonly bool $is_active,
        public readonly EventSexEnum $sex,
        public readonly string $slug,
        public readonly ?string $description,
        /** @var GuidePageData[]|null */
        public readonly ?array $event_guide_pages,
        public readonly ?string $video_link,
        public readonly ?string $registration_welcome_video,
        public readonly ?string $registration_welcome_text,
        public readonly ?string $registration_before_your_event_text,
        /** @var ScheduleBlockData[]|null */
        public readonly ?array $event_schedule,
        /** @var FaqData[]|null */
        public readonly ?array $event_faqs,
        public readonly string $image,
        public readonly ?string $hero_image,
        public readonly ?string $hero_video,
        /** @var string[]|null Asset paths in the `app` container */
        public readonly ?array $photos,
        public readonly ?string $summary,
        public readonly ?string $summary_link,
        /** @var KeyDetailData[]|null */
        public readonly ?array $key_details,
        /** @var ActionButtonData[]|null */
        public readonly ?array $action_buttons,
        /** @var array<int, array<string, mixed>>|null Unused jsonb column: no blueprint field writes it and no consumer reads it */
        #[LiteralTypeScriptType('Array<Record<string, unknown>> | null')]
        public readonly ?array $new_content,
        public ?string $welcome_nav_text_override,
        public ?string $before_event_nav_text_override,
        public ?string $schedule_nav_text_override,
        public ?string $faq_nav_text_override,
    ) {}

    /**
     * Create a new instance from an EventType model.
     */
    public static function fromModel(?EventType $eventType): ?self
    {
        return new self(
            id: $eventType->id,
            allow_materials_order: $eventType->allow_materials_order ?? false,
            number_of_items_in_material_order_label: $eventType->number_of_items_in_material_order_label,
            group_type: $eventType->group_type,
            type: $eventType->type,
            name: $eventType->name,
            filter_name: $eventType->filter_name,
            order: $eventType->order ?? 0,
            free_gift_image: $eventType->free_gift_image,
            zapier_webhook_url: $eventType->zapier_webhook_url,
            card_teaser_text: $eventType->card_teaser_text,
            tags: $eventType->tags,
            is_active: $eventType->is_active ?? true,
            sex: $eventType->sex ?? EventSexEnum::MEN,
            slug: $eventType->slug,
            description: $eventType->description,
            event_guide_pages: self::collectOrNull(GuidePageData::class, $eventType->event_guide_pages),
            video_link: $eventType->video_link,
            registration_welcome_video: $eventType->registration_welcome_video,
            registration_welcome_text: $eventType->registration_welcome_text,
            registration_before_your_event_text: $eventType->registration_before_your_event_text,
            event_schedule: self::collectOrNull(ScheduleBlockData::class, $eventType->event_schedule),
            event_faqs: self::collectOrNull(FaqData::class, $eventType->event_faqs),
            image: $eventType->image,
            hero_image: $eventType->hero_image,
            hero_video: $eventType->hero_video,
            photos: $eventType->photos,
            summary: $eventType->summary,
            summary_link: $eventType->summary_link,
            key_details: self::collectOrNull(KeyDetailData::class, $eventType->key_details),
            action_buttons: self::collectOrNull(ActionButtonData::class, $eventType->action_buttons),
            new_content: $eventType->new_content,
            welcome_nav_text_override: $eventType->welcome_nav_text_override,
            before_event_nav_text_override: $eventType->before_event_nav_text_override,
            schedule_nav_text_override: $eventType->schedule_nav_text_override,
            faq_nav_text_override: $eventType->faq_nav_text_override,
        );
    }
}
