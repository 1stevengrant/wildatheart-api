<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class AutoUnpublishUnconfirmedFiresJob implements ShouldBeUnique, ShouldQueue
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
        $ninetyDaysAgo = now()->subDays(90);

        Event::query()
            ->with(['user', 'managers'])
            ->allFires()
            ->lastCheckinReminderSent()
            ->where('last_checkin_reminder_sent_at', '<=', $threeDaysAfterReminder)
            // Only unpublish if initial email was sent within last 90 days
            // Events with email 90+ days ago will get a fresh initial email instead
            ->where('last_checkin_email_sent_at', '>', $ninetyDaysAgo)
            ->lastCheckinNotConfirmed()
            ->published()
            ->notAutoUnpublished()
            ->chunkById(100, function ($events) {
                foreach ($events as $event) {
                    UnpublishSingleUnconfirmedFireJob::dispatch($event);
                }
            });
    }
}
