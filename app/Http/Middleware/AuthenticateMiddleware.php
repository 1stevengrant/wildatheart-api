<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use App\Exceptions\ApiException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class AuthenticateMiddleware extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @throws ApiException
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($this->auth->guard()->guest()) {
            throw new ApiException('Requires authentication', 401);
        }

        return null;
    }
}
