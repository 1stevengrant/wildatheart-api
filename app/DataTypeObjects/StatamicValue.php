<?php

namespace App\DataTypeObjects;

use Spatie\LaravelData\Data;
use Statamic\Fields\Value as FieldValue;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class StatamicValue extends Data
{
    public function __construct(
        public string $value = '',
        public string $label = '',
        public string $key = '',
    ) {}

    public static function fromFieldValue(FieldValue $fieldValue): self
    {
        $raw = (string) $fieldValue;

        return new self(
            value: $raw,
            label: $raw,
            key: $raw,
        );
    }

    public static function fromString(string $value): self
    {
        return new self(
            value: $value,
            label: $value,
            key: $value,
        );
    }
}
