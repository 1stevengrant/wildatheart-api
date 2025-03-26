<?php

namespace App\Observers;

use App\Models\Redirect;
use App\Services\RedirectService;

class RedirectObserver
{
    public function __construct(private RedirectService $redirectService) {}

    public function created(Redirect $redirect): void
    {
        $source = $redirect->source;
        $destination = $redirect->destination;

        $this->redirectService->createInCloudflare($source, $destination);
    }

    public function updated(Redirect $redirect): void
    {
        $this->created($redirect);
    }

    public function deleted(Redirect $redirect): void
    {
        $source = $redirect->source;
        $this->redirectService->deleteFromCloudflare($source);
    }
}
