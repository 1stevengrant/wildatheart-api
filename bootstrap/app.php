<?php

use Sentry\Laravel\Integration;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;
use App\Http\Middleware\Auth0ApiMiddleware;
use App\Http\Middleware\Auth0JwtMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Spatie\Permission\Middleware\PermissionMiddleware;
use App\Http\Middleware\EnsureProfileIsCompleteMiddleware;
use App\Http\Middleware\HasAgreedToHostAgreementMiddleware;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        // channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('non-inertia')
                ->group(base_path('routes/non_inertia.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function () {
            session()->put('auth.intended_url', request()->url());

            return route('login');
        });
        $middleware->redirectUsersTo(AppServiceProvider::HOME);

        $middleware->validateCsrfTokens(except: [
            'webhooks/sendgrid',
            'api/v1/webhooks/*',
            'api/v1/events/*/connect',
        ]);

        $middleware->web(HandleInertiaRequests::class);

        $middleware->throttleApi();

        $middleware->group('non-inertia', [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'agreed_to_host_agreement' => HasAgreedToHostAgreementMiddleware::class,
            'auth0.api' => Auth0ApiMiddleware::class,
            'auth0.jwt' => Auth0JwtMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role' => RoleMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'super_admin' => SuperAdminMiddleware::class,
            'profile_complete' => EnsureProfileIsCompleteMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        Integration::handles($exceptions);
    })->create();
