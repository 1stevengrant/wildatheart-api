<?php

namespace App\Jobs;

use Exception;
use App\Services\RedirectService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class CreateRedirectInCloudflareJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public string $source,
        public string $destination
    ) {}

    public function handle(RedirectService $redirectService): void
    {
        $redirectService->createInCloudflare($this->source, $this->destination);
    }

    public function middleware(): array
    {
        return [
            new WithoutOverlapping("redirect-create-{$this->source}"),
        ];
    }

    public function failed(Exception $exception): void
    {
        // Log the failure for monitoring
        logger()->error('Failed to create redirect in CloudFlare', [
            'source' => $this->source,
            'destination' => $this->destination,
            'error' => $exception->getMessage(),
        ]);
    }
}
