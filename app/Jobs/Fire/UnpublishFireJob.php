<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UnpublishFireJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Event $event)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->event->unpublish();

        activity('fire')
            ->performedOn($this->event)
            ->log('Fire unpublished automatically');
    }
}
