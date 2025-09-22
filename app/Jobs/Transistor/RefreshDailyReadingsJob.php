<?php

namespace App\Jobs\Transistor;

use App\Services\TransistorService;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefreshDailyReadingsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public bool $rebuildAstro = false,
    ) {}

    public function handle(TransistorService $transistorService): void
    {
        if ($this->rebuildAstro) {
            // Wait a minute to ensure Transistors cache is updated.
            sleep(60);
        }

        $transistorService->refreshEpisodeList('wildatheart-daily', 'published');
        $transistorService->refreshEpisodeList('wildatheart-daily', 'scheduled');
        $transistorService->refreshEpisodeList('wildatheart-daily', 'draft');

        if ($this->rebuildAstro) {
            Http::post('https://api.cloudflare.com/client/v4/pages/webhooks/deploy_hooks/60bfbe99-ab1b-44e2-8856-d5eeb228bb9a');
        }
    }
}
