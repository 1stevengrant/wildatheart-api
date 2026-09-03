<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ResolveContactInvitationData extends Data
{
    public function __construct(public string $token) {}

    public static function rules(): array
    {
        return ['token' => ['required', 'string', 'size:43']];
    }
}
