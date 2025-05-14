<?php

namespace App\Http\Integrations\WeGive\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

class UpdateDonorRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method
     */
    protected Method $method = Method::PUT;

    /**
     * Constructor for the request
     *
     * @param  int  $donorId  The ID of the donor to update
     * @param  array  $data  The data to update the donor with
     */
    public function __construct(
        protected int $donorId,
        protected array $data
    ) {}

    /**
     * Define the endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "donors/$this->donorId";
    }

    /**
     * Define the body of the request
     */
    protected function defaultBody(): array
    {
        return $this->data;
    }
}
