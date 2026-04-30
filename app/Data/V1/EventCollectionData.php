<?php

namespace App\Data\V1;

use App\Models\Event;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventCollectionData extends Data
{
    public function __construct(
        public string $id,
        public string $location,
        public ?string $dates,
        public string $sex,
        public string $eventType,
        public string $eventTypeImage,
        public string $eventTypeCardDescription,
        public string $eventGroupType,
        public string $hostName,
        public ?string $hostAvatar,
        public ?float $longitude,
        public ?float $latitude,
        public ?string $newCardHeading,
        public ?float $distance,
    ) {}

    public static function fromEvent(Event $event): self
    {
        return new self(
            id: $event->id,
            location: $event->location,
            dates: $event->eventType->group_type === 'fire' ? null : $event->formatted_dates,
            sex: $event->sex->value,
            eventType: $event->eventType->name,
            eventTypeImage: $event->eventType->new_image,
            eventTypeCardDescription: $event->eventTypeCardDescription,
            eventGroupType: $event->eventType->group_type,
            hostName: $event->user->fullname ?? '',
            hostAvatar: $event->user->profilePhoto(),
            longitude: $event->display_longitude ?? $event->longitude,
            latitude: $event->display_latitude ?? $event->latitude,
            newCardHeading: $event->eventType->card_description,
            distance: $event->distance ? round((float) $event->distance) : null,
        );
    }
}
