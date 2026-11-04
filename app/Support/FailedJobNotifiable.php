<?php

namespace App\Support;

use Throwable;
use Spatie\FailedJobMonitor\Notifiable;

/**
 * The notifier runs inside the JobFailed event, ahead of the listener that
 * writes the failed_jobs row. An exception escaping from here would lose that
 * row, the remaining channels and the job's original exception.
 */
class FailedJobNotifiable extends Notifiable
{
    public function notify($instance): void
    {
        foreach ($instance->via($this) as $channel) {
            try {
                $this->notifyNow($instance, [$channel]);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
