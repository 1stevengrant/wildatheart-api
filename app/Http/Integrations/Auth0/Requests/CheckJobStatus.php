<?php

namespace App\Http\Integrations\Auth0\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class CheckJobStatus extends Request
{
  public function __construct(protected string $jobId) {}

  /**
   * The HTTP method of the request
   */
  protected Method $method = Method::GET;

  /**
   * The endpoint for the request
   */
  public function resolveEndpoint(): string
  {
    return '/jobs/' . $this->jobId;
  }
}
