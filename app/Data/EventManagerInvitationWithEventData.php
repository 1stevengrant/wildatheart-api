<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Models\EventManagerInvitation;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventManagerInvitationWithEventData extends Data
{
    public function __construct(
        public string $id,
        public EventData $event,
        public string $invited_by_name,
    ) {}

    public static function fromModel(EventManagerInvitation $invitation): self
    {
        return new self(
            id: $invitation->id,
            event: EventData::fromModel($invitation->event),
            invited_by_name: $invitation->invitedByUser->fullname,
        );
    }
}
