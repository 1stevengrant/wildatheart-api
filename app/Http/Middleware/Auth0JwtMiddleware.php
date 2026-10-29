<?php

namespace App\Http\Middleware;

use Closure;
use Throwable;
use Illuminate\Http\Request;
use App\Actions\User\ProvisionAuth0User;
use App\DataTransferObjects\Auth0Profile;
use App\Exceptions\UserProvisioningException;
use App\Services\Auth0\JwtAccessTokenVerifier;
use Auth0\SDK\Exception\InvalidTokenException;
use Symfony\Component\HttpFoundation\Response;

class Auth0JwtMiddleware
{
    public function __construct(
        private readonly JwtAccessTokenVerifier $verifier,
        private readonly ProvisionAuth0User $provisionUser,
    ) {}

    /**
     * Verify an Auth0 JWT access token and lazily provision the local user.
     *
     * On 401, sets a WWW-Authenticate header with an `error=...` code so
     * callers can tell which check fired without leaking signature details.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->unauthenticated('missing_bearer');
        }

        try {
            $claims = $this->verifier->verify($token);
        } catch (InvalidTokenException $exception) {
            return $this->unauthenticated('invalid_token', $exception->getMessage());
        } catch (Throwable $exception) {
            return $this->unauthenticated('token_verification_failed', $exception->getMessage());
        }

        $sub = $claims['sub'] ?? null;

        if (! is_string($sub) || $sub === '') {
            return $this->unauthenticated('missing_subject');
        }

        try {
            $user = $this->provisionUser->handle($sub, Auth0Profile::fromClaims($claims), $token);
        } catch (UserProvisioningException $exception) {
            return $this->unauthenticated($exception->errorCode);
        }

        auth()->login($user);

        return $next($request);
    }

    private function unauthenticated(string $errorCode, ?string $detail = null): Response
    {
        $body = ['message' => 'Unauthenticated.', 'error' => $errorCode];

        if ($detail && ! app()->environment('production')) {
            $body['detail'] = $detail;
        }

        return response()
            ->json($body, 401)
            ->header('WWW-Authenticate', sprintf('Bearer error="%s"', $errorCode));
    }
}
