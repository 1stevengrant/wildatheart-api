<?php

namespace App\Exceptions;

use Exception;

class UserProvisioningException extends Exception
{
    private function __construct(
        public readonly string $errorCode,
    ) {
        parent::__construct("User provisioning failed: {$this->errorCode}");
    }

    public static function noEmailAvailable(): self
    {
        return new self('user_provisioning_failed');
    }

    public static function emailNotVerified(): self
    {
        return new self('email_not_verified');
    }

    public static function accountDisabled(): self
    {
        return new self('account_disabled');
    }
}
