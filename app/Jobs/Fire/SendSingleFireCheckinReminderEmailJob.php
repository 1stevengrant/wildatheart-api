<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendSingleFireCheckinReminderEmailJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $retryAfter = 300;

    public function __construct(public Event $event)
    {
        //
    }

    public function uniqueId(): string
    {
        return $this->event->id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Update timestamp FIRST to prevent duplicate emails on retry
        $this->event->update(['last_checkin_reminder_sent_at' => now()]);

        activity('fire')
            ->performedOn($this->event)
            ->log('Check-in reminder email sent');

        // Send to primary host with managers CC'd
        $mail = Mail::to($this->event->user->email);

        if ($this->event->managers->isNotEmpty()) {
            $mail->cc($this->event->managers->pluck('email'));
        }

        $mail->send(new \App\Mail\Fire\SevenDayFollowupMailable($this->event));
    }
}
