<?php

namespace App\Jobs\Fire;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

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
        Mail::to($this->event->host)
            ->send(new \App\Mail\Fire\MonthlyFireCleanupEmailToHostMailable($this->event));

        $this->event->update(['last_monthly_email_sent_at' => $this->lastMonthlySentAtDate()]);
    }

    protected function lastMonthlySentAtDate()
    {
        if (! app()->isProduction()) {
            return now()->subDays(10);
        }

        return now();
    }
}
