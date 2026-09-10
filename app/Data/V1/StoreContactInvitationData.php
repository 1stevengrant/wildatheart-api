<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Illuminate\Http\UploadedFile;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;
use Spatie\TypeScriptTransformer\Attributes\TypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;

#[TypeScript]
class StoreContactInvitationData extends Data
{
    public function __construct(
        public string $given_name,
        public string $family_name,
        public string $email,
        public ?string $phone,
        #[TypeScriptType('string[]')]
        public array $event_ids,
        #[LiteralTypeScriptType('File | null')]
        public ?UploadedFile $image = null,
    ) {}

    public static function rules(): array
    {
        return [
            'given_name' => ['required', 'string', 'min:2', 'max:100'],
            'family_name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', app()->environment('testing') ? 'email:rfc' : 'email:rfc,dns', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'event_ids' => ['required', 'array', 'min:1'],
            'event_ids.*' => ['required', 'uuid', 'distinct', 'exists:events,id'],
            'image' => ['nullable', 'image', 'max:5120', 'mimes:jpg,jpeg,png'],
        ];
    }
}
