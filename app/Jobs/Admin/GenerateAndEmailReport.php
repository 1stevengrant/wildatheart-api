<?php

namespace App\Jobs\Admin;

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
        private readonly string $email,
        private readonly ReportName $report,
        private readonly string $reportTitle,
        private readonly ReportFormat $format = ReportFormat::CSV,
    ) {}

    public function handle(ReportService $reportService): void
    {
        $reportCSV = $reportService->generate($this->report, $this->format);

        // Handle null or empty CSV content
        if ($reportCSV === null) {
            $reportCSV = '';
        }

        $storagePath = 'tmp/report.csv';

        // Ensure the tmp directory exists
        Storage::makeDirectory('tmp');

        // TODO: Store files in S3 and provide secure link in email.
        Storage::put($storagePath, $reportCSV);

        $filePath = Storage::path($storagePath);

        $date = now()->format('m/d/Y');

        Mail::to($this->email)
            ->cc('nic@wildatheart.org')
            ->send(new \App\Mail\ReportMailable($date, $filePath, $this->reportTitle));

        Storage::delete($storagePath);
    }
}
