<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFireCheckinsEmailJob implements ShouldQueue
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
                $event->update([
                    'last_checkin_email_sent_at' => now(),
                    'last_checkin_reminder_sent_at' => null,
                ]);
            });
    }
}
