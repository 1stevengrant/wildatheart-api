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

    /**
     * @param  string|array<string>  $emails
     */
    public function __construct(
        private readonly string|array $emails,
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

        // Use local disk for temporary files (needed for email attachments even when main storage is S3)
        $localDisk = Storage::disk('local');

        // Ensure the tmp directory exists
        $localDisk->makeDirectory('tmp');

        // Store file on local disk for attachment
        $localDisk->put($storagePath, $reportCSV);

        $filePath = $localDisk->path($storagePath);

        $date = now()->format('m/d/Y');

        // Normalize emails to array
        $emails = is_array($this->emails) ? $this->emails : [$this->emails];

        Mail::to($emails)
            ->cc('nic@wildatheart.org')
            ->send(new \App\Mail\ReportMailable($date, $filePath, $this->reportTitle));

        // Clean up local temporary file
        $localDisk->delete($storagePath);
    }
}
