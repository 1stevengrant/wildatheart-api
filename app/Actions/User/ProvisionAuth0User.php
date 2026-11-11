<?php

namespace App\Actions\User;

use Throwable;
use App\Models\User;
use App\Services\Auth0\UserinfoClient;
use App\DataTransferObjects\Auth0Profile;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Jobs\User\UpdateProfileFromAuth0Job;
use App\Exceptions\UserProvisioningException;
use Illuminate\Database\UniqueConstraintViolationException;

class ProvisionAuth0User
{
    use AsAction;

    public function __construct(
        private readonly UserinfoClient $userinfoClient,
    ) {}

    /**
     * Resolve the local user for the given JWT subject, creating one from the
     * profile (or /userinfo as a fallback) if necessary.
     *
     * The provider_id (Auth0 sub) is the only signal we trust unconditionally.
     * Anything that uses the email claim to find/create a local user also
     * requires email_verified=true: an unverified Auth0 signup with a
     * victim's email must not link to or create the victim's local account.
     *
     * On staging, a verified staff address also makes the user a super admin, checked on
     * every request so someone who verifies after their first sign in is picked up too.
     *
     * @throws UserProvisioningException
     */
    public function handle(string $sub, Auth0Profile $profile, string $accessToken): User
    {
        $user = $this->resolveUser($sub, $profile, $accessToken);

        GrantStaffSuperAdmin::run($user, $profile->emailVerified ? $profile->email : null);

        return $user;
    }

    /**
     * @throws UserProvisioningException
     */
    private function resolveUser(string $sub, Auth0Profile &$profile, string $accessToken): User
    {
        // withTrashed: soft-deleted rows still hold the unique email index, so
        // resurrecting one is the only safe path. The host app uses SoftDeletes
        // on User, hence the explicit scope override.
        $user = User::withTrashed()->where('provider_id', $sub)->first();

        if ($user) {
            return $this->ensureActive($user);
        }

        if (! $profile->email) {
            // First request for this sub AND no email claim in the access token —
            // fall back to /userinfo (the access token's aud includes the
            // userinfo audience, so Auth0 will honour this bearer). One-shot
            // per user; subsequent requests resolve by provider_id above.
            $profile = $this->userinfoClient->fetchProfile($accessToken) ?? $profile;
        }

        if (! $profile->email) {
            throw UserProvisioningException::noEmailAvailable();
        }

        if (! $profile->emailVerified) {
            throw UserProvisioningException::emailNotVerified();
        }

        $existing = User::withTrashed()->where('email', $profile->email)->first();

        if ($existing) {
            return $this->linkExistingUser($existing, $sub);
        }

        return $this->createUser($sub, $profile);
    }

    /**
     * @throws UserProvisioningException
     */
    private function linkExistingUser(User $existing, string $sub): User
    {
        if ($existing->trashed()) {
            // Soft-deleted local user matched by a verified Auth0 email.
            // Refuse: deletion was deliberate, and resurrecting via an
            // email-only match opens an account-takeover path for anyone
            // who can prove control of the email post-deletion.
            throw UserProvisioningException::accountDisabled();
        }

        $existing->update([
            'provider_id' => $sub,
            'provider' => $existing->provider ?? 'auth0',
        ]);

        return $existing;
    }

    /**
     * @throws UserProvisioningException
     */
    private function createUser(string $sub, Auth0Profile $profile): User
    {
        try {
            $user = User::create([
                'provider_id' => $sub,
                'provider' => 'auth0',
                'email' => $profile->email,
                'given_name' => $profile->givenName,
                'family_name' => $profile->familyName,
            ]);
        } catch (UniqueConstraintViolationException) {
            return $this->resolveRacedUser($sub, $profile);
        }

        $this->syncProfileFromAuth0($user);

        return $user;
    }

    /**
     * Copy the phone number collected at Auth0 signup onto a new user right away,
     * so the next screen (e.g. accepting a host invitation) doesn't ask for it again.
     * A failure here must not block sign-in.
     */
    private function syncProfileFromAuth0(User $user): void
    {
        try {
            // Run inline on this instance so the request that provisioned the user already sees the phone.
            (new UpdateProfileFromAuth0Job($user))->handle();
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * A concurrent request inserted a row for this sub or email between our
     * SELECT and INSERT. Re-resolve and let the winner stand — but still
     * refuse if the winner is a trashed email match.
     *
     * @throws UserProvisioningException
     */
    private function resolveRacedUser(string $sub, Auth0Profile $profile): User
    {
        $raced = User::withTrashed()
            ->where(function ($query) use ($sub, $profile) {
                $query->where('provider_id', $sub)
                    ->orWhere('email', $profile->email);
            })
            ->first();

        if (! $raced) {
            throw UserProvisioningException::noEmailAvailable();
        }

        if ($raced->trashed() && $raced->provider_id !== $sub) {
            throw UserProvisioningException::accountDisabled();
        }

        return $this->ensureActive($raced);
    }

    private function ensureActive(User $user): User
    {
        if ($user->trashed()) {
            $user->restore();
        }

        return $user;
    }
}
