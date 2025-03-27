<?php

namespace App\Jobs;

use App\Services\ReportName;
use App\Services\ReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailLocalReport implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $email)
    {
        //
    }

    public function handle(ReportService $reportService): void
    {
        $reportCSV = $reportService->generate(ReportName::BAKR_HOSTS);

        $filePath = storage_path('app/tmp/report.csv');
        $storagePath = "tmp/report.csv";
        Storage::put($storagePath, $reportCSV);

        $date = now()->format("m/d/Y");

        Mail::to($this->email)
            ->cc('nic@wildatheart.org')
            ->send(new \App\Mail\ReportMailable($date, $filePath, "Becoming A King Retreat Hosts"));

        Storage::delete($storagePath);
    }
}
