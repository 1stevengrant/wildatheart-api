<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class EventInterestData extends Data
{
    public function __construct(
        public string $event_id,
        public int $user_id,
        public string $created_at,
    ) {}

    public static function fromPivot(Pivot $pivot): self
    {
        return new self(
            event_id: (string) $pivot->event_id,
            user_id: (int) $pivot->user_id,
            created_at: $pivot->created_at->toIso8601String(),
        );
    }
}
