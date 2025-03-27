<?php

namespace App\Console\Commands;

use App\Jobs\EmailLocalReport;
use App\Enums\ReportName;
use Illuminate\Console\Command;

class GenerateReport extends Command
{
    protected $signature = 'app:generate-report {email} {report} {--title=}';

    protected $description = 'Generates and emails a CSV report to the provided emails';

    public function handle()
    {
        $email = $this->argument('email');
        $report = ReportName::from($this->argument('report'));
        $title = $this->option('title');

        EmailLocalReport::dispatch($email, $report, $title);
        $this->info('CSV report generated and emailed successfully');
    }
}
