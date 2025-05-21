<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use App\Mail\EventAutoUnpublishedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class AutoUnpublishUnconfirmedFiresJob implements ShouldQueue
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
        $threeDaysAfterReminder = now()->subDays(3);

        Event::query()
            ->with(['user'])
            ->allFires()
            ->lastCheckinReminderSent()
            ->where('last_checkin_reminder_sent_at', '<=', $threeDaysAfterReminder)
            ->hasNotConfirmedCheckin()
            ->published()
            ->notAutoUnpublished()
            ->each(function (Event $event) {
                $event->update([
                    'approved_at' => null,
                    'auto_unpublished' => true,
                ]);

                Mail::to($event->user->email)->send(new EventAutoUnpublishedMail($event));
            });
    }
}
