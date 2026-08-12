<?php

namespace App\Listeners;

use App\Models\EventType;
use Statamic\Events\EntrySaved;
use App\Observers\EventTypeObserver;

class ClearEventTypeCacheOnEntrySavedListener
{
    /**
     * The event type endpoints cache their fully serialised payload, which
     * embeds each type's related landing page. Editing one of those entries
     * would otherwise not surface until the cache expired.
     */
    public function handle(EntrySaved $event): void
    {
        $isLandingPage = EventType::query()
            ->where('related_landing_page_id', $event->entry->id())
            ->exists();

        if (! $isLandingPage) {
            return;
        }

        EventTypeObserver::forgetCache();
    }
}
