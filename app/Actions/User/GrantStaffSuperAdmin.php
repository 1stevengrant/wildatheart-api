<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Makes staff super admins on staging (and local) so they can use the internal tools
 * without someone flipping users.super by hand. Only an address Auth0 says is verified
 * counts, and the domain has to match exactly: x@evilwildatheart.org and
 * x@wildatheart.org.evil.com stay ordinary users. Never runs in production.
 */
class GrantStaffSuperAdmin
{
    use AsAction;

    public function handle(User $user, ?string $verifiedEmail): void
    {
        if (app()->isProduction()) {
            return;
        }

        if (! app()->environment(config('auth.staff_super_admins.environments'))) {
            return;
        }

        if ($user->super) {
            return;
        }

        if (! $this->isStaffAddress($verifiedEmail)) {
            return;
        }

        $user->forceFill(['super' => true])->save();
    }

    private function isStaffAddress(?string $email): bool
    {
        if (! $email || ! str_contains($email, '@')) {
            return false;
        }

        $domain = mb_strtolower(Str::afterLast(mb_trim($email), '@'));
        $staffDomains = array_map(mb_strtolower(...), config('auth.staff_super_admins.domains'));

        return in_array($domain, $staffDomains, true);
    }
}
