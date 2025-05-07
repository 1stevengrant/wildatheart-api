<?php

namespace App\Http\Integrations\WeGive\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

class SubscribeToCommunicationListRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method
     */
    protected Method $method = Method::POST;

    /**
     * Constructor for the request
     */
    public function __construct(
        protected string $communicationListId,
        protected string $email,
        protected string $firstName,
        protected string $lastName,
        protected ?string $phone = null,
        protected string $type = 'individual'
    ) {}

    /**
     * Define the endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return "/communication-lists/$this->communicationListId/subscribe";
    }

    public function defaultBody(): array
    {
        return [
            'type' => $this->type,
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
        ];
    }
}
