<?php

namespace App\Http\Resources;

use JsonSerializable;
use Statamic\Http\Resources\API\EntryResource;

class PublicEntryResource extends EntryResource
{
    private const INTERNAL_FIELDS = [
        'api_url',
        'edit_url',
        'updated_by',
    ];

    public function toArray($request): array
    {
        return $this->withoutInternalFields(parent::toArray($request));
    }

    private function withoutInternalFields(mixed $value): mixed
    {
        if ($value instanceof JsonSerializable) {
            return $this->withoutInternalFields($value->jsonSerialize());
        }

        if (! is_array($value)) {
            return $value;
        }

        return array_map(
            fn (mixed $field): mixed => $this->withoutInternalFields($field),
            array_diff_key($value, array_flip(self::INTERNAL_FIELDS)),
        );
    }
}
