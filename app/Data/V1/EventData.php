<?php

namespace App\Data\V1;

use App\Models\Event;
use App\Models\EventType;
use Spatie\LaravelData\Data;
use App\Support\Schedule\PublicScheduleDates;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use App\DataTypeObjects\API\V1\Content\Events\EventsShowResponse;

#[TypeScript]
class EventData extends Data
{
    public function __construct(
        public string $id,
        public string $location,
        public string $latitude,
        public string $longitude,
        public ?string $dates,
        public string $sex,
        public string $eventType,
        public string $eventGroupType,
        public string $eventTypeImage,
        public string $eventTypeDescription,
        /** @var string[] Asset paths in the `app` container */
        public array $eventTypePhotos,
        public ?string $scheduledFor,
        public string $hostName,
        public ?string $hostAvatar,
        #[TypeScriptType('\\' . EventsShowResponse::class . '|null')]
        public ?array $landingPage,
        public EventTypePageData $eventTypePage,
        public string $connect_url,
        public ?NextEventDateData $nextDate = null,
        /** @var array<int, NextEventDateData> */
        public array $upcomingDates = [],
    ) {}

    public static function fromEvent(Event $event): self
    {
        return new self(
            id: $event->id,
            location: $event->location,
            latitude: $event->latitude,
            longitude: $event->longitude,
            dates: $event->eventType->group_type === EventType::TYPE_FIRE ? null : $event->formatted_dates,
            sex: $event->sex->value,
            eventType: $event->eventType->name,
            eventGroupType: $event->eventType->group_type,
            eventTypeImage: $event->eventType->image,
            eventTypeDescription: $event->eventType->page_description_text ?? '',
            eventTypePhotos: $event->eventType->photos ?? [],
            scheduledFor: $event->badge_display,
            hostName: $event->user->fullname ?? '',
            hostAvatar: $event->user->profilePhoto(),
            landingPage: null,
            eventTypePage: EventTypePageData::fromEventType($event->eventType),
            connect_url: $event->connect_url,
            nextDate: PublicScheduleDates::next($event),
            upcomingDates: PublicScheduleDates::upcoming($event) ?? [],
        );
    }
}
