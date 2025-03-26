<?php

namespace App\Jobs;

use App\Services\RedirectService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AddRedirect implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $source, public string $destination) {}

    /**
     * Execute the job.
     */
    public function handle(RedirectService $redirectService): void
    {
        $redirectService->create($this->source, $this->destination);
    }
}
