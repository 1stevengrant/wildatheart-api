<?php

namespace App\Jobs;

use App\Services\RedirectService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

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
