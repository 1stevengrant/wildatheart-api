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

    /** Rebuild after the daily readings refresh, delayed an hour so the episode list is fresh. */
    public const string DAILY_READINGS = 'daily-readings';

    /** Rebuild right after a CMS content change, such as saving a prayer. */
    public const string CONTENT = 'content';

    public int $tries = 2;

    /**
     * Keep the uniqueness lock held for the same span as the dispatch delay
     * in RefreshDailyReadingsJob, so repeated triggers within that window
     * collapse into a single rebuild instead of each queuing their own. An
     * immediate rebuild releases its lock as soon as it has run.
     */
    public int $uniqueFor = 3600;

    /** @var array<int, int> */
    public array $backoff = [60, 120];

    /**
     * Each source dedupes only against itself, so a pending daily readings
     * rebuild never swallows a content rebuild, or the reverse.
     */
    public function __construct(public string $source = self::CONTENT) {}

    public function uniqueId(): string
    {
        return $this->source;
    }

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
