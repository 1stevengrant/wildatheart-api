<?php

namespace App\Jobs;

use Closure;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
        public ?Model $performedOn = null,
        public ?Closure $tap = null,
        public ?string $event = '',
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $activity = activity()
            ->inLog($this->logName)
            ->causedBy($this->causedBy)
            ->event($this->event)
            ->withProperties($this->properties);

        if ($this->performedOn instanceof Model) {
            $activity->performedOn($this->performedOn);
        }

        if ($this->tap) {
            $activity->tap($this->tap);
        }

        $activity->log($this->description);
    }
}
