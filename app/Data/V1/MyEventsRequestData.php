<?php

namespace App\Data\V1;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class MyEventsRequestData extends Data
{
    public function __construct(
        public string $status = 'active',
        public int $page = 1,
        public int $per_page = 20,
    ) {}

    public static function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
