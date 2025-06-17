<?php

namespace App\Observers;

use App\Models\Redirect;
use App\Jobs\CreateRedirectInCloudflareJob;
use App\Jobs\DeleteRedirectFromCloudflareJob;

readonly class RedirectObserver
{
    public function created(Redirect $redirect): void
    {
        CreateRedirectInCloudflareJob::dispatch($redirect->source, $redirect->destination);
    }

    public function updated(Redirect $redirect): void
    {
        CreateRedirectInCloudflareJob::dispatch($redirect->source, $redirect->destination);
    }

    public function deleted(Redirect $redirect): void
    {
        DeleteRedirectFromCloudflareJob::dispatch($redirect->source);
    }
}
