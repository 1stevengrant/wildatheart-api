<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class Auth0ApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Validates an Auth0 Bearer token by calling the Auth0 userinfo endpoint,
     * then resolves the corresponding local user and logs them in.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            Log::warning('Auth0ApiMiddleware: missing bearer token', [
                'path' => $request->path(),
            ]);

            return response()->json(['message' => 'Unauthenticated.', 'reason' => 'missing_bearer_token'], 401);
        }

        $auth0Domain = config('auth0.base_url');
        $response = Http::withToken($token)->get("{$auth0Domain}/userinfo");

        if ($response->failed()) {
            Log::warning('Auth0ApiMiddleware: userinfo call failed', [
                'path' => $request->path(),
                'auth0_domain' => $auth0Domain,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json(['message' => 'Unauthenticated.', 'reason' => 'userinfo_failed', 'status' => $response->status()], 401);
        }

        $auth0User = $response->json();

        if (! isset($auth0User['email'])) {
            Log::warning('Auth0ApiMiddleware: userinfo response missing email', [
                'path' => $request->path(),
                'keys' => array_keys($auth0User ?? []),
            ]);

            return response()->json(['message' => 'Unauthenticated.', 'reason' => 'missing_email'], 401);
        }

        $user = User::where('email', $auth0User['email'])->first();

        if (! $user) {
            Log::warning('Auth0ApiMiddleware: no local user for email', [
                'path' => $request->path(),
                'email' => $auth0User['email'],
            ]);

            return response()->json(['message' => 'Unauthenticated.', 'reason' => 'no_local_user'], 401);
        }

        auth()->login($user);

        return $next($request);
    }
}
