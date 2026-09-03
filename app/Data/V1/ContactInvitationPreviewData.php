<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class ContactInvitationPreviewData extends Data
{
    /** @param array<int, ContactInvitationEventData> $events */
    public function __construct(
        public string $host_name,
        #[LiteralTypeScriptType('App.Data.V1.ContactInvitationEventData[]')]
        public array $events,
        public string $expires_at,
    ) {}
}
