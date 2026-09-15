<?php

namespace App\Data\Concerns;

use Spatie\LaravelData\Data;

trait CollectsNullableData
{
    /**
     * Statamic stores an untouched grid or replicator as null, so keep null
     * as null and only build data objects when there are rows to build from.
     *
     * @template TData of Data
     *
     * @param  class-string<TData>  $dataClass
     * @param  array<int, array<string, mixed>>|null  $items
     * @return TData[]|null
     */
    protected static function collectOrNull(string $dataClass, ?array $items): ?array
    {
        if ($items === null) {
            return null;
        }

        return $dataClass::collect(array_values($items));
    }
}
