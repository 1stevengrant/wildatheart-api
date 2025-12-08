<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AutoUnpublishUnconfirmedFiresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $retryAfter = 300;

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
            ->lastCheckinNotConfirmed()
            ->published()
            ->notAutoUnpublished()
            ->each(function (Event $event) {
                UnpublishSingleUnconfirmedFireJob::dispatch($event);
            });
    }
}
