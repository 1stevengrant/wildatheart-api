<?php

namespace App\DataTransferObjects;

class Auth0Profile
{
    public function __construct(
        public readonly ?string $email,
        public readonly bool $emailVerified,
        public readonly ?string $givenName,
        public readonly ?string $familyName,
    ) {}

    /**
     * Build a profile from verified JWT claims, checking both bare and
     * namespaced ("{audience}/{key}") claim names.
     *
     * @param  array<string, mixed>  $claims
     */
    public static function fromClaims(array $claims): self
    {
        $email = self::stringClaim($claims, 'email');

        return new self(
            email: $email ? mb_strtolower($email) : null,
            emailVerified: self::boolClaim($claims, 'email_verified') === true,
            givenName: self::stringClaim($claims, 'given_name'),
            familyName: self::stringClaim($claims, 'family_name'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromUserinfo(array $data): self
    {
        $email = isset($data['email']) && is_string($data['email']) && $data['email'] !== ''
            ? mb_strtolower($data['email'])
            : null;

        return new self(
            email: $email,
            emailVerified: ($data['email_verified'] ?? null) === true,
            givenName: is_string($data['given_name'] ?? null) ? $data['given_name'] : null,
            familyName: is_string($data['family_name'] ?? null) ? $data['family_name'] : null,
        );
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private static function stringClaim(array $claims, string $key): ?string
    {
        foreach (self::claimCandidates($key) as $candidate) {
            if (isset($claims[$candidate]) && is_string($claims[$candidate]) && $claims[$candidate] !== '') {
                return $claims[$candidate];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $claims
     */
    private static function boolClaim(array $claims, string $key): ?bool
    {
        foreach (self::claimCandidates($key) as $candidate) {
            if (! array_key_exists($candidate, $claims)) {
                continue;
            }

            $value = $claims[$candidate];

            if (is_bool($value)) {
                return $value;
            }

            if (is_string($value)) {
                return in_array(mb_strtolower($value), ['true', '1'], true);
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private static function claimCandidates(string $key): array
    {
        $namespace = mb_rtrim((string) config('auth0.api.audience'), '/');

        return [$key, "{$namespace}/{$key}"];
    }
}
