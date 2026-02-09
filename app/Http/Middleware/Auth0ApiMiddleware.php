<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $auth0Domain = config('auth0.base_url');
        $response = Http::withToken($token)->get("{$auth0Domain}/userinfo");

        if ($response->failed()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $auth0User = $response->json();

        if (! isset($auth0User['email'])) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $user = User::where('email', $auth0User['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        auth()->login($user);

        return $next($request);
    }
}
