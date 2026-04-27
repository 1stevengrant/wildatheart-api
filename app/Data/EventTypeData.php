<?php

namespace App\Data;

use App\Models\EventType;
use App\Enums\EventSexEnum;
use App\Enums\EventTypeEnum;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventTypeData extends Data
{
    public function __construct(
        public int $id,
        public readonly bool $allow_materials_order,
        public readonly ?string $number_of_items_in_material_order_label,
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
        public readonly ?array $event_guide_pages,
        public readonly ?string $video_link,
        public readonly ?string $registration_welcome_video,
        public readonly ?string $registration_welcome_text,
        public readonly ?string $registration_before_your_event_text,
        public readonly ?array $event_schedule,
        public readonly ?array $event_faqs,
        public readonly string $image,
        public readonly ?string $hero_image,
        public readonly ?string $hero_video,
        public readonly ?array $photos,
        public readonly ?string $summary,
        public readonly ?string $summary_link,
        public readonly ?array $key_details,
        public readonly ?array $action_buttons,
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
            sex: match (true) {
                $eventType->sex instanceof EventSexEnum => $eventType->sex,
                is_string($eventType->sex) && $eventType->sex === 'men' => EventSexEnum::MEN,
                is_string($eventType->sex) && $eventType->sex === 'women' => EventSexEnum::WOMEN,
                default => EventSexEnum::MEN,
            },
            slug: $eventType->slug,
            description: $eventType->description,
            event_guide_pages: $eventType->event_guide_pages,
            video_link: $eventType->video_link,
            registration_welcome_video: $eventType->registration_welcome_video,
            registration_welcome_text: $eventType->registration_welcome_text,
            registration_before_your_event_text: $eventType->registration_before_your_event_text,
            event_schedule: $eventType->event_schedule,
            event_faqs: $eventType->event_faqs,
            image: $eventType->image,
            hero_image: $eventType->hero_image,
            hero_video: $eventType->hero_video,
            photos: $eventType->photos,
            summary: $eventType->summary,
            summary_link: $eventType->summary_link,
            key_details: $eventType->key_details,
            action_buttons: $eventType->action_buttons,
            new_content: $eventType->new_content,
            welcome_nav_text_override: $eventType->welcome_nav_text_override,
            before_event_nav_text_override: $eventType->before_event_nav_text_override,
            schedule_nav_text_override: $eventType->schedule_nav_text_override,
            faq_nav_text_override: $eventType->faq_nav_text_override,
        );
    }
}
