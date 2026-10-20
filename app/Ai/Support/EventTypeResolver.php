<?php

namespace App\Ai\Support;

use App\Models\EventType;

class EventTypeResolver
{
    /**
     * Resolve event type ids from a mix of ids, slugs and names.
     *
     * @param  array<int, int|string>  $values
     * @return array<int, int>
     */
    public static function ids(array $values): array
    {
        $values = array_map(fn (int|string $value) => mb_strtolower(mb_trim((string) $value)), $values);

        return EventType::query()
            ->get(['id', 'name', 'slug'])
            ->filter(fn (EventType $eventType) => in_array((string) $eventType->id, $values, true)
                || in_array(mb_strtolower($eventType->slug), $values, true)
                || in_array(mb_strtolower($eventType->name), $values, true))
            ->pluck('id')
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return EventType::query()->pluck('name', 'id')->all();
    }
}
