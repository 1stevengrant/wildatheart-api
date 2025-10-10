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
        public string $dates,
        public string $sex,
        public string $eventType,
        public string $eventTypeImage,
        public string $eventTypeCardDescription,
        public string $hostName,
        public ?string $hostAvatar,
        public ?float $longitude,
        public ?float $latitude,
    ) {}

    public static function fromEvent(Event $event): self
    {
        return new self(
            id: $event->id,
            location: $event->location,
            dates: $event->formatted_dates,
            sex: $event->sex->value,
            eventType: $event->eventType->name,
            eventTypeImage: $event->eventType->image,
            eventTypeCardDescription: $event->eventTypeCardDescription,
            hostName: $event->user->fullname ?? '',
            hostAvatar: $event->user->profilePhoto(),
            longitude: $event->longitude,
            latitude: $event->latitude,
        );
    }
}
