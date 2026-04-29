<?php

namespace App\Exceptions;

use Statamic\Exceptions\ControlPanelExceptionHandler;

/**
 * Statamic's CP handler redirects authorization failures to `cp_route('login')`,
 * but this app uses Auth0 instead of Statamic's built-in login, so that route
 * isn't registered. Redirect to the app's Auth0 login route instead.
 */
class CpExceptionHandler extends ControlPanelExceptionHandler
{
    protected function getAuthExceptionRedirectUrl()
    {
        return route('login');
    }
}
