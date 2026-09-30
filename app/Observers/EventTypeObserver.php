<?php

namespace App\Observers;

use App\Models\EventType;

class EventTypeObserver
{
    public const array CACHE_KEYS = [
        'allEventTypes.v2',
        'experienceTypes.v2',
        'retreatTypes.v2',
        'fireTypes.v2',
        'eventTypesGroupedByType.v3',
        'eventTypesWithCounts.serialised.v3',
        'event_types.v2',
        'event_types_list.v2',
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
