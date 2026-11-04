<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Transistor\RefreshDailyReadingsJob;

class RefreshDailyReadings extends Command
{
    protected $signature = 'daily-readings:refresh
                            {--rebuild-astro : Trigger Astro rebuild after refreshing}
                            {--delay= : Delay job execution in seconds (default: 0 for immediate)}';

    protected $description = 'Refresh daily readings from Transistor API';

    /** SQS rejects a DelaySeconds above 900. */
    private const MAX_DELAY_SECONDS = 900;

    public function handle()
    {
        $rebuildAstro = $this->option('rebuild-astro');
        $delay = (int) $this->option('delay');

        if ($delay > self::MAX_DELAY_SECONDS) {
            $this->warn("Delay of {$delay} seconds exceeds the queue maximum, using " . self::MAX_DELAY_SECONDS . ' seconds instead.');

            $delay = self::MAX_DELAY_SECONDS;
        }

        $job = RefreshDailyReadingsJob::dispatch($rebuildAstro);

        if ($delay > 0) {
            $job->delay(now()->addSeconds($delay));
            $this->info("Daily readings refresh job dispatched with {$delay} second delay.");
        } else {
            $this->info('Daily readings refresh job dispatched immediately.');
        }

        if ($rebuildAstro) {
            $this->info('Astro rebuild will be triggered after completion.');
        }

        return Command::SUCCESS;
    }
}
