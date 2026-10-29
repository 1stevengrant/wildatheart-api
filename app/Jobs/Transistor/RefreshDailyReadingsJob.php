<?php

namespace App\Jobs\Transistor;

use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Cloudflare\TriggerAstroBuildJob;

class RefreshDailyReadingsJob implements ShouldQueue
{
    use Queueable;

    public const SHOW_ID = 'wildatheart-daily';

    public const STATUSES = ['published', 'scheduled', 'draft'];

    /** SQS rejects a DelaySeconds above 900. */
    public const ASTRO_BUILD_DELAY_SECONDS = 900;

    public int $timeout = 60;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public bool $rebuildAstro = false,
    ) {}

    public function handle(): void
    {
        foreach (self::STATUSES as $status) {
            RefreshEpisodeListJob::dispatch(self::SHOW_ID, $status);
        }

        if ($this->rebuildAstro) {
            TriggerAstroBuildJob::dispatch()->delay(now()->addSeconds(self::ASTRO_BUILD_DELAY_SECONDS));
        }
    }
}
