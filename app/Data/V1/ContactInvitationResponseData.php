<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class ContactInvitationResponseData extends Data
{
    public function __construct(
        #[LiteralTypeScriptType("'invitation_pending'")]
        public string $status,
        public string $invitation_id,
        public string $expires_at,
    ) {}
}
