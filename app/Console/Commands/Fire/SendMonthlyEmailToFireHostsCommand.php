<?php

namespace App\Console\Commands\Fire;

use App\Jobs\Fire\MonthlyFireCleanupEmailToHostJob;
use App\Models\Event;
use Illuminate\Console\Command;

class SendMonthlyEmailToFireHostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-monthly-email-to-fire-hosts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends an email each month to all fire hosts to check if they are still interested in hosting a fire.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fires = Event::query()
            ->published()
            ->fires()
            ->updatedAgo(90)
            ->get();

        $fires->each(function ($fire) {
            MonthlyFireCleanupEmailToHostJob::dispatch($fire);
            if (config('app.env') === 'local') {
                dd();
            }
        });

    }
}
