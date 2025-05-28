<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendFireCheckinsEmailJob implements ShouldQueue
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
        $ninetyDaysAgo = now()->subDays(90);

        Event::query()
            ->with(['user'])
            ->allFires()
            ->published()
            ->where(function ($query) use ($ninetyDaysAgo) {
                $query->whereNull('last_checkin_email_sent_at')
                    ->orWhere('last_checkin_email_sent_at', '<=', $ninetyDaysAgo);
            })
            ->updatedAgo(90)
            ->where(function ($query) use ($ninetyDaysAgo) {
                $query->whereNull('last_checkin_confirmed_at')
                    ->orWhere('last_checkin_confirmed_at', '<=', $ninetyDaysAgo);
            })
            ->each(function (Event $event) {
                MonthlyFireCleanupEmailToHostJob::dispatch($event);
            });
    }
}
