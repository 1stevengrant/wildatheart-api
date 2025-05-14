<?php

namespace App\Http\Integrations\Auth0\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

class UpdateUserRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the HTTP method
     */
    protected Method $method = Method::PATCH;

    /**
     * Constructor for the request
     *
     * @param  string  $userId  The Auth0 user ID to update
     * @param  array  $data  The data to update the user with
     */
    public function __construct(
        protected string $userId,
        protected array $data
    ) {}

    /**
     * Define the endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        // Use URL encoding for the user ID as it might contain special characters
        $encodedUserId = urlencode($this->userId);

        return "users/$encodedUserId";
    }

    /**
     * Define the body of the request
     *
     * Auth0 allows these fields:
     * - blocked: boolean
     * - email_verified: boolean
     * - email: string
     * - phone_number: string
     * - phone_verified: boolean
     * - user_metadata: object
     * - app_metadata: object
     * - given_name: string
     * - family_name: string
     * - name: string
     * - nickname: string
     * - picture: string
     * - verify_email: boolean
     * - verify_phone_number: boolean
     * - password: string
     * - connection: string
     * - client_id: string
     * - username: string
     */
    protected function defaultBody(): array
    {
        return $this->data;
    }

    /**
     * Define the headers for the request
     */
    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
