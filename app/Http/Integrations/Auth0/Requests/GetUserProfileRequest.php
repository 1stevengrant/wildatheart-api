<?php

namespace App\Http\Integrations\Auth0\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetUserProfileRequest extends Request
{
    public function __construct(protected string $userId) {}

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/users/' . $this->userId;
    }
}
