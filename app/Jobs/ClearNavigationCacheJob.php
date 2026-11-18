<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Services\NavigationDataService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ClearNavigationCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?string $globalHandle = null,
        public ?User $user = null
    ) {}

    public function handle(NavigationDataService $navigation): void
    {
        $keysCleared = $navigation->forgetCached();

        LogActivityJob::dispatch(
            logName: 'cache',
            description: 'Navigation cache cleared',
            properties: [
                'trigger' => $this->globalHandle ?? 'manual',
                'keys_cleared' => $keysCleared,
            ],
            causedBy: $this->user,
            statamicGlobalHandle: $this->globalHandle
        );
    }
}
