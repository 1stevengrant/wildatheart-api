<?php

namespace App\Support;

use Statamic\Entries\Entry;
use Statamic\Facades\Blink;
use Illuminate\Support\Collection;
use App\Builders\EntryQueryBuilder;
use Spatie\Blink\Blink as BlinkStore;

class MediaItemRouteLookups
{
    public static function experienceForSession(?string $sessionId): ?Entry
    {
        if (blank($sessionId)) {
            return null;
        }

        return self::store()->once('experiences-by-session', fn (): Collection => EntryQueryBuilder::experiences()
            ->get()
            ->flatMap(fn (Entry $experience): array => collect($experience->get('sessions', []))
                ->map(fn (string $sessionId): array => [$sessionId, $experience])
                ->all())
            ->unique(fn (array $pair): string => $pair[0])
            ->mapWithKeys(fn (array $pair): array => [$pair[0] => $pair[1]]))
            ->get($sessionId);
    }

    public static function sessionNumber(Entry $experience, string $sessionId): int
    {
        $index = self::store()->once("session-ids.{$experience->id()}", fn (): Collection => $experience->sessions
            ->map(fn (Entry $session): string => $session->id())
            ->values())
            ->search($sessionId);

        return $index === false
            ? 1
            : $index + 1;
    }

    public static function podcastForSeries(string $seriesId): ?Entry
    {
        return self::store()->once('podcasts-by-series', fn (): Collection => EntryQueryBuilder::podcasts()
            ->get()
            ->filter(fn (Entry $podcast): bool => filled($podcast->get('subsplash_series_id')))
            ->unique(fn (Entry $podcast): string => $podcast->get('subsplash_series_id'))
            ->keyBy(fn (Entry $podcast): string => $podcast->get('subsplash_series_id')))
            ->get($seriesId);
    }

    public static function flush(): void
    {
        self::store()->flush();
    }

    private static function store(): BlinkStore
    {
        return Blink::store('media-item-route-lookups');
    }
}
