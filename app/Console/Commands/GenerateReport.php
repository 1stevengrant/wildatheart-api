<?php

namespace App\Console\Commands;

use App\Jobs\EmailLocalReport;
use Illuminate\Console\Command;

class GenerateReport extends Command
{
    protected $signature = 'app:generate-report {email}';

    protected $description = 'Generates and emails a CSV report to the provided emails';

    public function handle()
    {
        $email = $this->argument('email');
        EmailLocalReport::dispatch($email);
        $this->info('CSV report generated and emailed successfully');
    }
}
