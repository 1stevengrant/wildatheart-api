<?php

namespace App\Jobs\Cloudflare;

use InvalidArgumentException;
use App\Actions\Astro\TriggerAstroBuild;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\FailOnException;

class TriggerAstroBuildJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    /**
     * Keep the uniqueness lock held for the same span as the dispatch delay
     * in RefreshDailyReadingsJob, so repeated triggers within that window
     * collapse into a single rebuild instead of each queuing their own.
     */
    public int $uniqueFor = 3600;

    /** @var array<int, int> */
    public array $backoff = [60, 120];

    public function middleware(): array
    {
        return [
            new FailOnException([InvalidArgumentException::class]),
        ];
    }

    public function handle(TriggerAstroBuild $triggerAstroBuild): void
    {
        $triggerAstroBuild->handle();
    }
}
