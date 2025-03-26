<?php

namespace App\Jobs;

use App\Services\ReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmailLocalReport implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $email)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ReportService $reportService): array
    {
        $result = $reportService->generate();
        $headers = [
            "given_name" => "First Name",
            "family_name" => "Last Name",
            "email" => "Email"
        ];

        $csv = $reportService->generateCSV($headers, $result->toArray());
        $filePath = storage_path('app/tmp/test.csv');
        Storage::put("tmp/test.csv", $csv);

        $date = now()->format("m/d/Y");

        Mail::to($this->email)
            ->send(new \App\Mail\ReportMailable($date, $filePath));

        return $result;
    }
}
