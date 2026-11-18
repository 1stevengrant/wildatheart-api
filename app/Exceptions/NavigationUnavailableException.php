<?php

namespace App\Exceptions;

use Exception;

class NavigationUnavailableException extends Exception
{
    public function __construct(string $message, public readonly string $handle)
    {
        parent::__construct($message);
    }

    public static function globalSetMissing(string $handle): self
    {
        $label = ucfirst(str_replace('_', ' ', $handle));

        return new self("{$label} global set not found", $handle);
    }

    public static function variablesMissing(string $handle): self
    {
        return new self('No navigation data found for default site', $handle);
    }

    public static function dataEmpty(string $handle): self
    {
        return new self('Navigation data is empty', $handle);
    }

    /** @return array{error: string, code: int, navigation_type: string} */
    public function toErrorPayload(): array
    {
        return [
            'error' => $this->getMessage(),
            'code' => 404,
            'navigation_type' => $this->handle,
        ];
    }
}
