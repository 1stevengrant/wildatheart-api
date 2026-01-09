<?php

namespace App\Data;

use Carbon\Carbon;
use App\Models\Event;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class InternalEventSearchData extends Data
{
    public function __construct(
        public string $id,
        public ?string $name,
        public ?string $host_name,
        public ?string $host_email,
        public ?string $host_phone,
        public ?string $city,
        public ?string $state,
        public ?string $event_type,
        public ?string $frequency,
        public ?int $attendee_count,
        public ?string $start_date,
        public float $distance,
    ) {}

    public static function fromModel(Event $event, float $distance): self
    {
        $startDate = $event->start_date;
        if ($startDate && ! $startDate instanceof Carbon) {
            $startDate = Carbon::parse($startDate);
        }

        return new self(
            id: $event->id,
            name: $event->name,
            host_name: $event->user?->name,
            host_email: $event->user?->email,
            host_phone: $event->user?->phone,
            city: $event->city,
            state: $event->state,
            event_type: $event->eventType?->name,
            frequency: $event->frequency?->value,
            attendee_count: $event->attendee_count,
            start_date: $startDate?->format('Y-m-d'),
            distance: round($distance, 2),
        );
    }
}
