<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class ContactInvitationResolutionData extends Data
{
    /** @param array<int, HostContactData> $data */
    public function __construct(
        #[LiteralTypeScriptType("'accepted' | 'already_accepted'")]
        public string $status,
        #[LiteralTypeScriptType('App.Data.V1.HostContactData[]')]
        public array $data = [],
    ) {}
}
