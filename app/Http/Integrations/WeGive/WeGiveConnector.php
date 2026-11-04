<?php

namespace App\Http\Integrations\WeGive;

use RuntimeException;
use Saloon\Http\Connector;
use Illuminate\Support\Str;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

class WeGiveConnector extends Connector
{
    public const ERROR_LOG_NAME = 'wegive_error';

    public const WARNING_LOG_NAME = 'wegive_warning';

    public const INFO_LOG_NAME = 'wegive';

    public const LOGGED_RESPONSE_LENGTH = 2000;

    public ?int $tries = 1;

    public static function isPermanentFailure(FatalRequestException|RequestException $exception): bool
    {
        if (! $exception instanceof RequestException) {
            return false;
        }

        if (in_array($exception->getStatus(), [408, 429], true)) {
            return false;
        }

        return $exception->getResponse()->clientError();
    }

    public static function loggableBody(string $body): string
    {
        return Str::limit($body, self::LOGGED_RESPONSE_LENGTH);
    }

    protected function defaultAuth(): TokenAuthenticator
    {
        $apiKey = config('services.wegive.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('WeGive API key is not configured. Set WEGIVE_API_KEY in the environment.');
        }

        return new TokenAuthenticator($apiKey);
    }

    public function resolveBaseUrl(): string
    {
        return 'https://api.wegive.com/api';
    }

    public function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

    }
}
