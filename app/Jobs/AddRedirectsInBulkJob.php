<?php

namespace App\Jobs;

use App\Services\RedirectService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AddRedirectsInBulkJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $file) {}

    /**
     * Execute the job.
     */
    public function handle(RedirectService $redirectService): void
    {
        $redirects = $redirectService->getRedirectsFromFile($this->file);
        $redirectService->createBulk($redirects);
    }
}
