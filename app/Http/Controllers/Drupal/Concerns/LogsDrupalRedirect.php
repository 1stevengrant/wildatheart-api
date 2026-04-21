<?php

namespace App\Http\Controllers\Drupal\Concerns;

use App\Jobs\LogActivityJob;

trait LogsDrupalRedirect
{
    private const LOG_NAME = 'redirects';

    private function logRedirect(string $path, ?object $user): void
    {
        LogActivityJob::dispatch(
            logName: self::LOG_NAME,
            description: "Redirected legacy Drupal URL to Laravel URL: {$path}",
            causedBy: $user,
        );
    }
}
