<?php

namespace App\Data\V1;

use App\Models\Event;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ContactInvitationEventData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $event_type_name,
    ) {}

    public static function fromModel(Event $event): self
    {
        return new self(
            id: $event->id,
            name: $event->name,
            event_type_name: $event->eventType->name,
        );
    }
}
