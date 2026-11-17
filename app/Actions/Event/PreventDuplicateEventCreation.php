<?php

namespace App\Actions\Event;

use Closure;
use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Exceptions\EventCreationException;

class PreventDuplicateEventCreation
{
    use AsAction;

    public const int WINDOW_SECONDS = 10;

    /**
     * @param  Closure(): Event  $create
     *
     * @throws EventCreationException
     */
    public function handle(User $host, Closure $create): Event
    {
        $event = Cache::lock("event-creation:{$host->id}", self::WINDOW_SECONDS)->get(function () use ($host, $create): Event {
            if ($host->events()->where('created_at', '>', now()->subSeconds(self::WINDOW_SECONDS))->exists()) {
                throw EventCreationException::duplicate();
            }

            return $create();
        });

        if ($event === false) {
            throw EventCreationException::duplicate();
        }

        return $event;
    }
}
