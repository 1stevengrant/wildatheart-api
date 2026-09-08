<?php

namespace App\Jobs\Transistor;

use DateTimeInterface;
use App\Services\TransistorService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Saloon\RateLimitPlugin\Exceptions\RateLimitReachedException;

class RefreshEpisodeListJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public int $maxExceptions = 3;

    public int $backoff = 30;

    public function __construct(
        public string $showId,
        public string $status,
    ) {}

    public function uniqueId(): string
    {
        return "{$this->showId}:{$this->status}";
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addHour();
    }

    public function handle(TransistorService $transistorService): void
    {
        try {
            $transistorService->refreshEpisodeList($this->showId, $this->status);
        } catch (RateLimitReachedException $exception) {
            $this->release(max(1, $exception->getLimit()->getRemainingSeconds()) + 1);
        }
    }
}
