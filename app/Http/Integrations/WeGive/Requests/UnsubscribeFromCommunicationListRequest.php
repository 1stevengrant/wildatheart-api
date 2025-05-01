<?php

namespace App\Http\Integrations\WeGive\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Traits\Body\HasJsonBody;

class UnsubscribeFromCommunicationListRequest extends Request implements HasBody
{
    use HasJsonBody;

    public function __construct(private readonly string $communicationListId, private readonly array $donorIds)
    {
    }

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return "/dashboard/communication-lists/$this->communicationListId/unsubscribe";
    }

    public function defaultBody(): array
    {
        return [
            'donors' => $this->donorIds,
        ];
    }
}
