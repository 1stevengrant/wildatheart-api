<?php

namespace App\Http\Integrations\WeGive\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\PendingRequest;

class TagDonorsRequest extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * Constructor for the tag donors request
     */
    public function __construct(
        protected array $donorIds,
        protected array $tagIds,
        protected bool $all = false
    ) {}

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/tags/tag-donors';
    }

    /**
     * Define the query parameters
     */
    protected function defaultQuery(): array
    {
        return [
            'all' => $this->all ? 'true' : 'false',
        ];
    }

    /**
     * Boot the request to add the array parameters
     */
    public function boot(PendingRequest $pendingRequest): void
    {
        foreach ($this->donorIds as $donorId) {
            $pendingRequest->query()->add('donors[]', (string) $donorId);
        }

        foreach ($this->tagIds as $tagId) {
            $pendingRequest->query()->add('tags[]', (string) $tagId);
        }
    }
}
