<?php

namespace App\Data;

use App\Models\Event;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class JoinAsHostEventData extends Data
{
    public function __construct(
        public string $token,
        public EventData $event,
        public string $invited_by_name,
        public bool $already_host = false,
    ) {}

    public static function fromEvent(Event $event, string $token, bool $alreadyHost = false): self
    {
        return new self(
            token: $token,
            event: EventData::fromModel($event),
            invited_by_name: $event->user->fullname,
            already_host: $alreadyHost,
        );
    }
}
