<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $logName = 'default',
        public string $description = 'Activity logged',
        public array $properties = [],
        public ?Model $causedBy = null,
        public ?Model $performedOn = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $activity = activity()
            ->inLog($this->logName)
            ->causedBy($this->causedBy)
            ->withProperties($this->properties);

        if ($this->performedOn instanceof Model) {
            $activity->performedOn($this->performedOn);
        }

        $activity->log($this->description);
    }
}
