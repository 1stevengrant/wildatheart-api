<?php

namespace App\Observers;

use JsonException;
use App\Models\Redirect;
use App\Services\RedirectService;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

readonly class RedirectObserver
{
    public function __construct(private RedirectService $redirectService) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function created(Redirect $redirect): void
    {
        $source = $redirect->source;
        $destination = $redirect->destination;

        $this->redirectService->createInCloudflare($source, $destination);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function updated(Redirect $redirect): void
    {
        $this->created($redirect);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function deleted(Redirect $redirect): void
    {
        $source = $redirect->source;
        $this->redirectService->deleteFromCloudflare($source);
    }
}
