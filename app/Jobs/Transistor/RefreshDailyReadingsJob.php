<?php

namespace App\Jobs\Transistor;

use App\Services\TransistorService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\Cloudflare\TriggerAstroBuildJob;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class RefreshDailyReadingsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public bool $rebuildAstro = false,
    ) {}

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping)
                ->releaseAfter(60) // Release lock after 60 seconds if job fails
                ->expireAfter(180) // Maximum time job can hold the lock (3 minutes)
        ];
    }

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
