<?php

namespace App\Jobs\Salesforce;

use App\Jobs\LogActivityJob;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleRecordDelete implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $data) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        dispatch(new LogActivityJob(
            logName: 'salesforce',
            description: 'contact deleted from Salesforce',
            properties: ['data' => $this->data],
            event: 'delete',
        ));
    }
}
