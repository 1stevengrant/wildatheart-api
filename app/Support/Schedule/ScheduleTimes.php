<?php

namespace App\Support\Schedule;

use Carbon\CarbonImmutable;

class ScheduleTimes
{
    /**
     * Converts a local wall-clock date and time into UTC. The wall clock is the source
     * of truth, so a 7pm Fire stays at 7pm across daylight saving changes.
     */
    public static function toUtc(string $date, string $time, string $timezone): CarbonImmutable
    {
        return CarbonImmutable::parse("{$date} {$time}", $timezone)->utc();
    }
}
