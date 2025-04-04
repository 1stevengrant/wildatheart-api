<?php

namespace App\Http\Integrations\Auth0\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Plugins\AcceptsJson;

class GetUserByEmail extends Request
{
    use AcceptsJson;

    public function __construct(protected string $email)
    {
    }

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/users';
    }

    protected function defaultQuery(): array
    {
        return [
            'q' => 'email:"' . $this->email . '"',
            'search_engine' => 'v3', // Required for Lucene syntax
        ];
    }
}
