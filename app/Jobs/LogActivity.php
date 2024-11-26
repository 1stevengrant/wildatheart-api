<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $logName = 'default',
        public string $description = 'Activity logged',
        public array $properties = [],
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        activity()
            ->inLog($this->logName)
            ->withProperties($this->properties)
            ->log($this->description);
    }
}
