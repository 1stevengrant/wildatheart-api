<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendFireCheckinsReminderEmailJob implements ShouldQueue
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
        $sevenDaysAgo = now()->subDays(7);

        Event::query()
            ->with(['user'])
            ->allFires()
            ->lastCheckinReminderNotSent()
            ->where('last_checkin_email_sent_at', '<=', $sevenDaysAgo)
            ->lastCheckinNotConfirmed()
            ->published()
            ->each(function (Event $event) {
                SendSingleFireCheckinReminderEmailJob::dispatch($event);
            });
    }
}
