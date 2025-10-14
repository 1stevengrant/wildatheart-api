<?php

namespace App\Jobs\Cloudflare;

use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TriggerAstroBuildJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Http::post('https://api.cloudflare.com/client/v4/pages/webhooks/deploy_hooks/60bfbe99-ab1b-44e2-8856-d5eeb228bb9a');
    }
}
