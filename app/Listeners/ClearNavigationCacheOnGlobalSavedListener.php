<?php

namespace App\Listeners;

use App\Jobs\ClearNavigationCacheJob;
use Statamic\Events\GlobalVariablesSaved;

class ClearNavigationCacheOnGlobalSavedListener
{
    /**
     * Navigation global handles that should trigger cache clearing
     *
     * @var array<string>
     */
    private const array NAVIGATION_GLOBALS = [
        'desktop_nav',
        'mobile_nav',
    ];

    public function handle(GlobalVariablesSaved $event): void
    {
        $handle = $event->variables->globalSet()->handle();

        if (! in_array($handle, self::NAVIGATION_GLOBALS)) {
            return;
        }

        ClearNavigationCacheJob::dispatch(
            globalHandle: $handle,
            user: auth()->user()
        );
    }
}
