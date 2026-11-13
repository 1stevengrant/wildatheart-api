<?php

namespace App\Queries;

use App\Models\EventType;

class MapEventTypesQuery
{
    /** @return array{
     *     allEventTypes: array<int, array<string, mixed>>,
     *     fireTypes: array<int, array<string, mixed>>
     * } */
    public static function get(): array
    {
        return [
            'allEventTypes' => cache()->remember('allEventTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->active()->orderBy('order')->get()->toArray();
            }),
            'fireTypes' => cache()->remember('fireTypes.v2', now()->addDay(), function (): array {
                return EventType::query()->where('group_type', EventType::TYPE_FIRE)->active()->orderBy('order')->get()->toArray();
            }),
        ];
    }
}
