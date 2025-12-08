<?php

namespace App\Data;

use App\Models\Event;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Lightweight DTO for map marker display.
 * Contains only the fields needed by LocalMapMarkers and its child components.
 */
#[TypeScript]
class EventMapData extends Data
{
    public function __construct(
        public string $id,
        public float $display_longitude,
        public float $display_latitude,
        public string $type_group,
        public string $url,
        public string $event_type_image,
        public string $location,
        public string $host_photo,
        public ?string $display_name,
        public string $host_name,
        public int $managers_count,
        public string $card_title,
        public string $badge_display,
    ) {}

    public static function fromModel(Event $event): self
    {
        return new self(
            id: $event->id,
            display_longitude: $event->display_longitude ?? $event->longitude,
            display_latitude: $event->display_latitude ?? $event->latitude,
            type_group: $event->eventType?->group_type ?? '',
            url: $event->url ?? '',
            event_type_image: $event->eventType?->image ?? '',
            location: $event->location ?? '',
            host_photo: $event->user?->profilePhoto() ?? '',
            display_name: $event->display_name,
            host_name: $event->host_name ?? '',
            managers_count: $event->managers?->count() ?? 0,
            card_title: $event->card_title ?? '',
            badge_display: $event->badge_display ?? '',
        );
    }
}
