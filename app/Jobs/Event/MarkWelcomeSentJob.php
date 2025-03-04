<?php

namespace App\Jobs\Event;

use App\Models\Event;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarkWelcomeSentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $eventId)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Event::findOrFail($this->eventId)->update([
            'welcome_sent' => true,
            'welcome_sent_at' => now(),
        ]);
    }
}
