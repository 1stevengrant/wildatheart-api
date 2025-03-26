<?php

namespace App\Http\Integrations\Cloudflare\Requests\KV;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

class PutRequestBulk extends Request implements HasBody
{
  use HasJsonBody;

  /** 
   * @param string $account
   * @param string $namespace
   * @param \App\DataTransferObjects\RedirectData[] $redirects 
   * */
  public function __construct(protected string $account, protected string $namespace, protected array $redirects) {}

  /**
   * The HTTP method of the request
   */
  protected Method $method = Method::PUT;

  /**
   * The endpoint for the request
   */
  public function resolveEndpoint(): string
  {
    return "/accounts/{$this->account}/storage/kv/namespaces/{$this->namespace}/bulk";
  }

  protected function defaultBody(): array
  {
    return collect($this->redirects)
      ->map(fn($redirect) => [
        'key' => $redirect->source,
        'value' => $redirect->destination
      ])
      ->toArray();
  }
}
