<?php

namespace App\Exceptions;

use App\Support\ReadOnlyMode;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReadOnlyModeException extends HttpException
{
    public function __construct()
    {
        parent::__construct(
            Response::HTTP_SERVICE_UNAVAILABLE,
            ReadOnlyMode::MESSAGE,
            headers: ['Retry-After' => ReadOnlyMode::RETRY_AFTER_SECONDS],
        );
    }
}
