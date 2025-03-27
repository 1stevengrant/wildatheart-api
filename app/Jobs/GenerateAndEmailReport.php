<?php

namespace App\Jobs;

use App\Enums\ReportName;
use App\Enums\ReportFormat;
use App\Services\ReportService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateAndEmailReport implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $email,
        private ReportName $report,
        private string $reportTitle,
        private ReportFormat $format = ReportFormat::CSV,
    ) {}

    public function handle(ReportService $reportService): void
    {
        $reportCSV = $reportService->generate($this->report, $this->format);

        $filePath = storage_path('app/tmp/report.csv');
        $storagePath = 'tmp/report.csv';
        Storage::put($storagePath, $reportCSV);

        $date = now()->format('m/d/Y');

        Mail::to($this->email)
            ->cc('nic@wildatheart.org')
            ->send(new \App\Mail\ReportMailable($date, $filePath, $this->reportTitle));

        Storage::delete($storagePath);
    }
}
