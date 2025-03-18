<?php

namespace App\Http\Integrations\WeGive\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTagsRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/tags';
    }
}
