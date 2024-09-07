<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PullProdDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pull-prod-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copies the production database to the local database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (app()->isProduction()) {
            $this->error('This command cannot be run in production!');

            return 1;
        }

        Artisan::call('db:wipe');
        $this->info('Local database wiped successfully!');
        shell_exec('./scripts/pullProductionDatabase.sh');

        $this->info('Production database pulled successfully!');

        return 1;
    }
}
