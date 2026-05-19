<?php

namespace App\Observers;

use App\Models\EventType;

class EventTypeObserver
{
    private const array CACHE_KEYS = [
        'allEventTypes',
        'experienceTypes',
        'retreatTypes',
        'fireTypes',
        'eventTypesGroupedByType',
        'event_types',
        'event_types_list',
    ];

    public function created(EventType $eventType): void
    {
        $this->forgetCache();
    }

    public function updated(EventType $eventType): void
    {
        $this->forgetCache();
    }

    public function deleted(EventType $eventType): void
    {
        $this->forgetCache();
    }

    protected function forgetCache(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            cache()->forget($key);
        }
    }
}
