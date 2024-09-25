<?php

namespace App\Services;

use Statamic\Entries\Entry;

class EntryBuilderService
{
    public function buildResponseFromEntry(Entry $entry)
    {
        $response = $entry->toArray();

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

        return $response;
    }
}
