<?php

namespace App\Services;

use Statamic\Fields\Value;
use Statamic\Entries\Entry;

class EntryBuilderService
{
    public function buildResponseFromEntry(Entry $entry)
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

    private function ensureArrays($data)
    {
        // If already an array, recursively process its items
        if (is_array($data)) {
            return array_map(fn($item) => $this->ensureArrays($item), $data);
        }

        // If it's a Statamic Value object, convert it using jsonSerialize
        if ($data instanceof Value) {
            $data = $data->jsonSerialize();

            // Recursively process the result
            return $this->ensureArrays($data);
        }

        // If it's an object with toArray method, convert it
        if (is_object($data) && method_exists($data, 'toArray')) {
            try {
                $data = $data->toArray();

                // Recursively process the result
                return $this->ensureArrays($data);
            } catch (\Exception $e) {
                // If toArray fails, continue to return the object as-is
            }
        }

        // Return the data as-is (could be a scalar, null, or an object without toArray)
        return $data;
    }
}
