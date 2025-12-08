<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MonthlyFireCleanupEmailToHostJob implements ShouldQueue
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
        activity('fire')
            ->performedOn($this->event)
            ->log('Monthly email sent');

        // Send to primary host
        Mail::to($this->event->user)
            ->send(new \App\Mail\Fire\MonthlyFireCleanupEmailToHostMailable($this->event));

        // Send to all hosts
        foreach ($this->event->managers as $manager) {
            Mail::to($manager)
                ->send(new \App\Mail\Fire\MonthlyFireCleanupEmailToHostMailable($this->event));
        }

        $this->event->update([
            'last_checkin_email_sent_at' => now(),
            'last_checkin_reminder_sent_at' => null,
        ]);
    }
}
