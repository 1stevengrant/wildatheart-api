<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ClearNavigationCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The cache keys to clear
     *
     * @var array<string>
     */
    private const NAVIGATION_CACHE_KEYS = [
        'navigation.desktop_nav.v2',
        'navigation.mobile_nav.v2',
        'navigation.all.v2',
    ];

    public function __construct(
        public ?string $globalHandle = null,
        public ?User $user = null
    ) {}

    public function handle(): void
    {
        $keysCleared = [];

        foreach (self::NAVIGATION_CACHE_KEYS as $key) {
            if (Cache::forget($key)) {
                $keysCleared[] = $key;
            }
        }

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
