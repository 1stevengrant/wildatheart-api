<?php

namespace App\Data\V1;

use App\Models\Event;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventCollectionData extends Data
{
    public function __construct(
        public string  $id,
        public string  $location,
        public string  $dates,
        public string  $sex,
        public string  $eventType,
        public string  $eventTypeImage,
        public string  $cardDescription,
        public string  $hostName,
        public ?string $hostAvatar,
    )
    {
    }

    public static function fromEvent(Event $event): self
    {
        return new self(
            id: $event->id,
            location: $event->location,
            dates: $event->formatted_dates,
            sex: $event->sex->value,
            eventType: $event->eventType->name,
            eventTypeImage: $event->eventType->image,
            cardDescription: $event->cardDescription,
            hostName: $event->user->fullname ?? '',
            hostAvatar: $event->user->profilePhoto(),
        );
    }
}
