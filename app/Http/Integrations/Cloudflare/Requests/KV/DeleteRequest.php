<?php

namespace App\Http\Integrations\Cloudflare\Requests\KV;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteRequest extends Request
{
  public function __construct(protected string $account, protected string $namespace, protected string $key) {}

  /**
   * The HTTP method of the request
   */
  protected Method $method = Method::DELETE;

  /**
   * The endpoint for the request
   */
  public function resolveEndpoint(): string
  {
    return "/accounts/{$this->account}/storage/kv/namespaces/{$this->namespace}/values/{$this->key}";
  }
}
