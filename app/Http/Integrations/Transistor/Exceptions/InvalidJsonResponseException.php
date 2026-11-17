<?php

namespace App\Http\Integrations\Transistor\Exceptions;

use Saloon\Http\Response;
use Saloon\Exceptions\Request\RequestException;

class InvalidJsonResponseException extends RequestException
{
    public static function fromResponse(Response $response): self
    {
        return new self($response, "Transistor returned an invalid JSON body (status {$response->status()}).");
    }
}
