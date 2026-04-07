<?php

namespace App\Http\Integrations\WeGive\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetDonorRequest extends Request
{
    public function __construct(protected string $donorId) {}

    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/dashboard/donors/' . $this->donorId;
    }
}
