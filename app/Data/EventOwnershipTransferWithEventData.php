<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use App\Models\EventOwnershipTransfer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventOwnershipTransferWithEventData extends Data
{
    public function __construct(
        public string $id,
        public EventData $event,
        public string $transferred_by_name,
    ) {}

    public static function fromModel(EventOwnershipTransfer $transfer): self
    {
        return new self(
            id: $transfer->id,
            event: EventData::fromModel($transfer->event),
            transferred_by_name: $transfer->transferred_by_name,
        );
    }
}
