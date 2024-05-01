<?php

namespace App\Console\Commands\Fire;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSevenDayFollowup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-seven-day-followup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Event::query()
            ->lastMonthlyEmailSentAgo(7)
            ->lastApprovedAt(90)
            ->get()
            ->each(function ($fire) {
                Mail::to($fire->host->email)
                    ->send(new \App\Mail\Fire\SevenDayFollowupMailable($fire));
            });
    }
}
