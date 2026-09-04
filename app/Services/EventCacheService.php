<?php

namespace App\Services;

class EventCacheService
{
    public function generateSearchCacheKey(array $validatedData): string
    {
        return 'events_search_v2_' . md5(json_encode($validatedData));
    }
}
