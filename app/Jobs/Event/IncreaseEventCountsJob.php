<?php

namespace App\Jobs\Event;

use App\Models\Event;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class IncreaseEventCountsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Event $event, public string $countType) {}

    public function handle(): void
    {
        $this->event->increment($this->countType);
    }
}
