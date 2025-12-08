<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use App\Mail\EventAutoUnpublishedMail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UnpublishSingleUnconfirmedFireJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $retryAfter = 300;

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
        $this->event->update([
            'approved_at' => null,
            'auto_unpublished' => true,
            'auto_unpublished_at' => now(),
        ]);

        // Send to primary host
        Mail::to($this->event->user->email)->send(new EventAutoUnpublishedMail($this->event));

        // Send to all hosts
        foreach ($this->event->managers as $manager) {
            Mail::to($manager->email)->send(new EventAutoUnpublishedMail($this->event));
        }
    }
}
