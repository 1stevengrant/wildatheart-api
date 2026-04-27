<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\Fire\AnnualFireCheckinMailable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class AnnualFireCheckinEmailJob implements ShouldBeUnique, ShouldQueue
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

    public function handle(): void
    {
        // Update timestamp FIRST to prevent duplicate emails on retry
        // This also enables the reminder and auto-unpublish jobs to pick up this event
        $this->event->update([
            'last_checkin_email_sent_at' => now(),
            'last_checkin_reminder_sent_at' => null,
        ]);

        activity('fire')
            ->performedOn($this->event)
            ->log('Annual check-in email sent');

        // Send to primary host with managers CC'd
        $mail = Mail::to($this->event->user);

        if ($this->event->managers->isNotEmpty()) {
            $mail->cc($this->event->managers->pluck('email'));
        }

        $mail->send(new AnnualFireCheckinMailable($this->event));
    }
}
