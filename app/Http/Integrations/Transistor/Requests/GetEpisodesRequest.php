<?php

namespace App\Http\Integrations\Transistor\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class GetEpisodesRequest extends Request implements Paginatable
{
    public function __construct(protected string $show_id) {}

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    protected function defaultQuery(): array
    {
        return [
            'show_id' => $this->show_id,

        ];
    }

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/episodes';
    }
}
