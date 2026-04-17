<?php

namespace App\Services;

use Statamic\Fields\Value;
use Statamic\Entries\Entry;
use Statamic\Fieldtypes\Link\ArrayableLink;

class EntryBuilderService
{
    public function buildResponseFromEntry(Entry $entry): array
    {
        // Get all relationship field handles from the blueprint
        $relationshipFields = $entry->blueprint()
            ->fields()->all()
            ->filter->isRelationship()
            ->keys()
            ->all();

        // Build the augmented response with relationships expanded
        $response = $entry
            ->toAugmentedCollection()
            ->withRelations($relationshipFields)
            ->withShallowNesting()
            ->toArray();

        // Remove unwanted properties
        $propertiesToRemove = [
            'api_url',
            'date',
            'is_entry',
            'origin_id',
            'order',
            'mount',
            'edit_url',
            'updated_by',
            'updated_at',
            'permalink',
            'origin',
            'last_modified',
        ];

        foreach ($propertiesToRemove as $property) {
            unset($response[$property]);
        }

        return $this->ensureArrays($response);
    }

    private function ensureArrays(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map(fn ($item) => $this->ensureArrays($item), $data);
        }

        if ($data instanceof Value) {
            return $this->ensureArrays($data->jsonSerialize());
        }

        if ($data instanceof ArrayableLink) {
            return (string) $data;
        }

        if (is_object($data) && method_exists($data, 'toArray')) {
            try {
                return $this->ensureArrays($data->toArray());
            } catch (\Exception $e) {
                // If toArray fails, return as-is
            }
        }

        return $data;
    }
}
