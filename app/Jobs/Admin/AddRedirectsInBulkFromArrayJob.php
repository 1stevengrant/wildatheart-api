<?php

namespace App\Jobs\Admin;

use App\Services\RedirectService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AddRedirectsInBulkFromArrayJob implements ShouldQueue
{
  use Queueable;

  /**
   * Create a new job instance.
   */
  public function __construct(public array $redirects) {}

  /**
   * Execute the job.
   */
  public function handle(RedirectService $redirectService): void
  {
    $redirectService->createBulk($this->redirects);
  }
}
