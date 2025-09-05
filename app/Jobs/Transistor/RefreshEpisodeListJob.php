<?php

namespace App\Jobs\Transistor;

use App\Services\TransistorService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefreshEpisodeListJob implements ShouldQueue
{
    use Queueable;

    public function __construct(

    ) {}

    public function handle(TransistorService $transistorService): void
    {
        $transistorService->refreshEpisodeList('wildatheart-daily');
    }
}
