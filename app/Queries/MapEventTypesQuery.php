<?php

namespace App\Queries;

use App\Models\EventType;

class MapEventTypesQuery
{
    /** @return array{
     *     allEventTypes: array<int, array<string, mixed>>,
     *     experienceTypes: array<int, array<string, mixed>>,
     *     retreatTypes: array<int, array<string, mixed>>,
     *     fireTypes: array<int, array<string, mixed>>
     * } */
    public static function get(): array
    {
        return [
            'allEventTypes' => cache()->remember('allEventTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->active()->orderBy('order')->get()->toArray();
            }),
            'experienceTypes' => cache()->remember('experienceTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->slug('%experience%')->active()->orderBy('order')->get()->toArray();
            }),
            'retreatTypes' => cache()->remember('retreatTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->slug('%retreat%')->active()->orderBy('order')->get()->toArray();
            }),
            'fireTypes' => cache()->remember('fireTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->slug('%fire%')->active()->orderBy('order')->get()->toArray();
            }),
        ];
    }
}
