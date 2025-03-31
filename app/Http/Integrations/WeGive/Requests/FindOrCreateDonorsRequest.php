<?php

namespace App\Http\Integrations\WeGive\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

class FindOrCreateDonorsRequest extends Request implements HasBody
{
    use HasJsonBody;

    public function __construct(
        protected string  $email,
        protected ?string  $firstName = null,
        protected ?string  $lastName = null,
        protected ?string $phone = null,
        protected ?array  $address = null
    ) {}

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/donors/find-or-create';
    }

    protected function defaultBody(): array
    {
        $body = [
            'type' => 'individual',
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
        ];

        if ($this->address !== null) {
            $body['address'] = $this->address;
        }

        return $body;
    }
}
