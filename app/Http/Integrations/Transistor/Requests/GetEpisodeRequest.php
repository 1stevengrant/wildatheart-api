<?php

namespace App\Http\Integrations\Transistor\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetEpisodeRequest extends Request
{

    public function __construct(protected ?string $episode_id = null) {}


    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    protected function defaultQuery(): array
    {
        return [];
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/episodes' . ($this->episode_id ? '/' . $this->episode_id : '');
    }
}
