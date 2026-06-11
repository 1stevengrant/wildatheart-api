<?php

namespace App\Services\Auth0;

use Auth0\SDK\Auth0;
use Auth0\SDK\Token;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\InvalidTokenException;

class JwtAccessTokenVerifier
{
    /**
     * Verify an Auth0-issued JWT access token and return its claims.
     *
     * Configures both the tenant domain (for JWKS lookup + fallback issuer)
     * and the custom domain (primary issuer match), so tokens issued via
     * either domain validate cleanly. The SDK Validator already handles
     * both string and array `aud` claims via array_intersect.
     *
     * @return array<string, mixed>
     *
     * @throws InvalidTokenException when the token is malformed, expired, or fails signature/claim validation
     */
    public function verify(string $jwt): array
    {
        $configuration = new SdkConfiguration([
            'strategy' => SdkConfiguration::STRATEGY_API,
            'domain' => (string) config('auth0.api.tenant_domain'),
            'customDomain' => (string) config('auth0.api.custom_domain'),
            'audience' => [(string) config('auth0.api.audience')],
            'tokenJwksUri' => (string) config('auth0.api.jwks_uri'),
            'tokenAlgorithm' => Token::ALGO_RS256,
        ]);

        $auth0 = new Auth0($configuration);

        return $auth0->decode(
            token: $jwt,
            tokenType: Token::TYPE_ACCESS_TOKEN,
        )->toArray();
    }
}
