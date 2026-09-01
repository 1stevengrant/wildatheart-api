<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Illuminate\Validation\Rule;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class HostContactsFilterData extends Data
{
    public function __construct(
        public ?string $search = null,
        public ?string $event = null,
        public ?string $archived = null,
        public ?string $added_from = null,
        public ?string $added_to = null,
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'added_from' => ['nullable', Rule::date()->format('Y-m-d')],
            'added_to' => ['nullable', Rule::date()->format('Y-m-d')],
        ];
    }
}
