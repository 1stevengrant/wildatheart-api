<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFireCheckinsReminderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $sevenDaysAgo = now()->subDays(7);

        Event::query()
            ->with(['user'])
            ->allFires()
            ->lastCheckinReminderNotSent()
            ->where('last_checkin_email_sent_at', '<=', $sevenDaysAgo)
            ->lastCheckinNotConfirmed()
            ->published()
            ->each(function (Event $event) {
                Mail::to($event->user->email)
                    ->send(new \App\Mail\Fire\SevenDayFollowupMailable($event));
                $event->update(['last_checkin_reminder_sent_at' => now()]);
            });
    }
}
