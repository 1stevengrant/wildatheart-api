<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Statamic\Entries\Entry;

class PublicMediaSeriesItem
{
    private const FIELDS = [
        'id',
        'title',
        'slug',
        'uri',
        'description',
        'media_type',
        'position',
        'is_coming_soon',
        'images',
    ];

    public static function fromEntry(Entry $entry): array
    {
        return Arr::only($entry->toArray(), self::FIELDS);
    }
}
