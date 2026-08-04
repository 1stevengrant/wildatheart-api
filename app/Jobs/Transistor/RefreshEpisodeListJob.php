<?php

namespace App\Jobs\Transistor;

use App\Services\TransistorService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefreshEpisodeListJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public string $showId,
        public string $status,
    ) {}

    public function handle(TransistorService $transistorService): void
    {
        $transistorService->refreshEpisodeList($this->showId, $this->status);
    }
}
