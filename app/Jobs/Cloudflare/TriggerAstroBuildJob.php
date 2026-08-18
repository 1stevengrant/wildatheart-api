<?php

namespace App\Jobs\Cloudflare;

use App\Actions\Astro\TriggerAstroBuild;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\FailOnException;
use InvalidArgumentException;

class TriggerAstroBuildJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    /** @var array<int, int> */
    public array $backoff = [60, 120];

    public function middleware(): array
    {
        return [
            new FailOnException([InvalidArgumentException::class]),
        ];
    }

    public function handle(TriggerAstroBuild $triggerAstroBuild): void
    {
        $triggerAstroBuild->handle();
    }
}
