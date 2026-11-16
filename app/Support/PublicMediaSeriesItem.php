<?php

namespace App\Support;

use Statamic\Entries\Entry;

class PublicMediaSeriesItem
{
    private const AUGMENTED_FIELDS = [
        'id',
        'title',
        'slug',
        'description',
        'media_type',
        'position',
        'is_coming_soon',
        'images',
    ];

    /** @return array<string, mixed> */
    public static function fromEntry(Entry $entry): array
    {
        $fields = array_values(array_intersect(self::AUGMENTED_FIELDS, $entry->augmented()->keys()));

        $item = [
            ...$entry->toAugmentedCollection($fields)->toArray(),
            'uri' => $entry->uri(),
        ];

        ksort($item);

        return $item;
    }
}
