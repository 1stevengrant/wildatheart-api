<?php

namespace App\Console\Commands\Fire;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendSevenDayFollowupCommand extends Command
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
    public function handle(): void
    {
        Event::query()
            ->lastMonthlyEmailSentAgo(7)
            ->lastApprovedAt(90, '<')
            ->get()
            ->each(function ($fire) {
                activity('fire')
                    ->performedOn($fire)
                    ->log('Followup email sent');
                Mail::to($fire->host->email)
                    ->send(new \App\Mail\Fire\SevenDayFollowupMailable($fire));
                if (config('app.env') === 'local') {
                    dd();
                }
            });
    }
}
