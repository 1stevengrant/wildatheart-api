<?php

namespace App\Actions\Astro;

use RuntimeException;
use InvalidArgumentException;
use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class TriggerAstroBuild
{
    use AsAction;

    public function handle(): void
    {
        if (app()->environment('local', 'testing')) {
            return;
        }

        $hook = config('cloudflare.astro_build_hook');

        if (! is_string($hook) || $hook === '') {
            throw new InvalidArgumentException('Cloudflare Astro build hook is not configured. Set CLOUDFLARE_ASTRO_BUILD_HOOK.');
        }

        $response = Http::post($hook);

        if ($response->failed()) {
            throw new RuntimeException('Failed to trigger the Astro production build: ' . $response->body());
        }
    }
}
