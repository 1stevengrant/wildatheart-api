<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Auth0\SDK\Auth0;
use Auth0\SDK\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class Auth0JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $auth0 = app(Auth0::class);

            $verifiedToken = $auth0->decode(
                token: $token,
                tokenType: Token::TYPE_ACCESS_TOKEN,
            );

            $claims = $verifiedToken->toArray();
            $sub = $claims['sub'] ?? null;

            if (! $sub) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Try direct provider_id lookup
            $user = User::where('provider_id', $sub)->first();

            // Fall back to email lookup via Auth0 userinfo
            if (! $user) {
                $userInfoResponse = Http::withToken($token)
                    ->get('https://auth.wildatheart.org/userinfo');

                if ($userInfoResponse->successful()) {
                    $userInfo = $userInfoResponse->json();
                    $email = $userInfo['email'] ?? null;
                    $primarySub = $userInfo['sub'] ?? null;

                    if ($primarySub && $primarySub !== $sub) {
                        $user = User::where('provider_id', $primarySub)->first();
                    }

                    if (! $user && $email) {
                        $user = User::where('email', $email)->first();
                    }
                }
            }

            if (! $user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            auth()->login($user);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
