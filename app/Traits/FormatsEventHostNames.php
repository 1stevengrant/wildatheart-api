<?php

namespace App\Traits;

/**
 * Provides methods for formatting event host names.
 *
 * Requires the using class to have:
 * - $event property (App\Models\Event) - The event instance with user and managers relationships
 */
trait FormatsEventHostNames
{
    protected function getAllHostNames(): string
    {
        $hosts = collect([$this->event->user]);

        if ($this->event->managers->isNotEmpty()) {
            $hosts = $hosts->merge($this->event->managers);
        }

        $names = $hosts->map(fn ($host) => $host->given_name)->filter();

        if ($names->count() <= 1) {
            return $names->first() ?? '';
        }

        if ($names->count() === 2) {
            return $names->implode(' and ');
        }

        $lastHost = $names->pop();

        return $names->implode(', ') . ', and ' . $lastHost;
    }
}
