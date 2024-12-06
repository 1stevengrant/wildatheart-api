<?php

namespace App\Console\Commands\Fire;

use App\Models\Event;
use Illuminate\Console\Command;
use App\Jobs\Fire\UnpublishFireJob;

class UnpublishInactiveFires extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:unpublish-inactive-fires';

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
            ->lastMonthlyEmailSentAgo(10)
            ->lastApprovedAt(90, '<')
            ->get()
            ->each(function ($fire) {
                UnpublishFireJob::dispatch($fire);
            });
    }
}
