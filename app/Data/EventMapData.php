<?php

namespace App\Data;

use App\Models\Event;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventMapData extends Data
{
    public function __construct(
        public string $id,
        public float $display_longitude,
        public float $display_latitude,
        public string $badge_display,
        public ?string $type_group,
        public string $card_title,
        public string $url,
        public string $location,
        public ?string $name,
        public ?string $display_name,
        public string $host_name,
        public string $host_photo,
        public string $event_type_image,
    ) {}

    public static function fromModel(Event $event): self
    {
        return new self(
            id: $event->id,
            display_longitude: $event->display_longitude ?? $event->longitude,
            display_latitude: $event->display_latitude ?? $event->latitude,
            badge_display: $event->badge_display,
            type_group: $event->eventType?->group_type,
            card_title: $event->card_title,
            url: $event->url,
            location: $event->location,
            name: $event->name,
            display_name: $event->display_name ?? null,
            host_name: $event->host_name,
            host_photo: $event->user->profilePhoto(),
            event_type_image: $event->eventType->image,
        );
    }
}
