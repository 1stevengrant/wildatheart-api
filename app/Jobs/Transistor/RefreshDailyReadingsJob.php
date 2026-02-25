<?php

namespace App\Jobs\Transistor;

use App\Services\TransistorService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Cloudflare\TriggerAstroBuildJob;

class RefreshDailyReadingsJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public bool $rebuildAstro = false,
    ) {}

    public function handle(TransistorService $transistorService): void
    {

        $transistorService->refreshEpisodeList('wildatheart-daily', 'published');
        $transistorService->refreshEpisodeList('wildatheart-daily', 'scheduled');
        $transistorService->refreshEpisodeList('wildatheart-daily', 'draft');

        if ($this->rebuildAstro) {
            // Dispatch the Cloudflare webhook after 1 hour
            TriggerAstroBuildJob::dispatch()->delay(now()->addHour());
        }
    }
}
