<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * The public event index and search responses are cached under keys that include a
 * generation token. Replacing the token retires every cached response at once, which
 * works on any cache store and doesn't need tags or a list of hashed search keys.
 */
class EventCacheService
{
    private const string GENERATION_KEY = 'events_cache_generation';

    public function collectionCacheKey(): string
    {
        return "events_collection.v3.{$this->generation()}";
    }

    public function generateSearchCacheKey(array $validatedData): string
    {
        return "events_search_v3_{$this->generation()}_" . md5(json_encode($validatedData));
    }

    /**
     * Inside a transaction this waits for the commit, so a request that lands in between
     * can't cache the old rows under the new generation.
     */
    public function flush(): void
    {
        DB::afterCommit(fn () => cache()->forever(self::GENERATION_KEY, Str::random(12)));
    }

    private function generation(): string
    {
        return cache()->rememberForever(self::GENERATION_KEY, fn () => Str::random(12));
    }
}
