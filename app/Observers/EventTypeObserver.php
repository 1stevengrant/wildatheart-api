<?php

namespace App\Observers;

use App\Models\EventType;

class EventTypeObserver
{
    public const array CACHE_KEYS = [
        'allEventTypes',
        'experienceTypes',
        'retreatTypes',
        'fireTypes',
        'eventTypesGroupedByType',
        'eventTypesWithCounts.serialised',
        'event_types',
        'event_types_list',
    ];

    public function created(EventType $eventType): void
    {
        self::forgetCache();
    }

    public function updated(EventType $eventType): void
    {
        self::forgetCache();
    }

    public function deleted(EventType $eventType): void
    {
        self::forgetCache();
    }

    public static function forgetCache(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            cache()->forget($key);
        }
    }
}
