<?php

namespace App\Console\Commands;

use App\Enums\ReportName;
use Illuminate\Console\Command;
use App\Jobs\GenerateAndEmailReport;
use App\Models\Event;

class GenerateReport extends Command
{
    protected $signature = 'app:generate-report {email} {report} {--title=}';

    protected $description = 'Generates and emails a CSV report to the provided emails';

    public function handle()
    {
        $email = $this->argument('email');
        $report = ReportName::from($this->argument('report'));
        $title = $this->option('title');

        GenerateAndEmailReport::dispatch($email, $report, $title);
        $this->info('CSV report generated and emailed successfully');
    }
}
