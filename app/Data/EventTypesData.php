<?php

namespace App\Data;

use App\Enums\EventSexEnum;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventTypesData extends Data
{
    public function __construct(
        public int $id,
        public readonly bool $allow_materials_order,
        public readonly ?string $number_of_items_in_material_order_label,
        public readonly ?string $group_type,
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
        public readonly ?string $image,
        public readonly ?string $hero_image,
        public readonly ?string $hero_video,
        public readonly ?array $photos,
        public readonly ?string $summary,
        public readonly ?array $key_details,
        public readonly ?array $action_buttons,
        public readonly ?array $new_content,
    ) {}
}
