<?php

namespace App\Services;

class EventCacheService
{
    public function legacySearchCacheKey(array $validatedData): string
    {
        return $this->searchCacheKey('legacy', $validatedData);
    }

    public function v1SearchCacheKey(array $validatedData): string
    {
        return $this->searchCacheKey('v1', $validatedData);
    }

    private function searchCacheKey(string $endpoint, array $validatedData): string
    {
        $hash = md5(json_encode($validatedData));

        return "events_search:{$endpoint}:{$hash}";
    }
}
