<?php

namespace App\Http\Integrations\Cloudflare\Requests\KV;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasStringBody;
use App\DataTransferObjects\RedirectData;

class PutRequest extends Request implements HasBody
{
    use HasStringBody;

    public function __construct(protected string $account, protected string $namespace, protected RedirectData $redirect) {}

    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::PUT;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        $encodedSource = urlencode($this->redirect->source);
        return "/accounts/{$this->account}/storage/kv/namespaces/{$this->namespace}/values/{$encodedSource}";
    }

    protected function defaultBody(): string
    {
        return $this->redirect->destination;
    }
}
