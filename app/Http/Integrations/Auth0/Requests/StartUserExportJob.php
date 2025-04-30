<?php

namespace App\Http\Integrations\Auth0\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;

class StartUserExportJob extends Request implements HasBody
{
  use HasJsonBody;

  public function __construct(
    protected string $connection_id,
    protected ?string $format = 'json',
    protected ?array $fields = null
  ) {}

  protected Method $method = Method::POST;

  public function resolveEndpoint(): string
  {
    return '/jobs/users-exports';
  }

  protected function defaultBody(): array
  {
    $body = [
      'connection_id' => $this->connection_id,
      'format' => $this->format,
      'fields' => $this->fields,
    ];


    return $body;
  }
}
