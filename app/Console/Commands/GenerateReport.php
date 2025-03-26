<?php

namespace App\Console\Commands;

use App\Jobs\EmailLocalReport;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class GenerateReport extends Command
{
    protected $signature = 'app:generate-report';

    protected $description = 'Command description';

    public function handle()
    {

        EmailLocalReport::dispatch("nic@nicbovee.com");
        $this->info('CSV report generated and emailed successfully');
    }
}
