<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait ResolvesAuthenticatedUser
{
    protected function getAuthenticatedUser(?string $email = null): ?User
    {
        $auth0User = Auth::guard('api')->user();
        $sub = $auth0User->id ?? null;

        $user = $sub ? User::where('provider_id', $sub)->first() : null;

        // Fall back to email lookup if provided (from Auth0 user info)
        if (! $user && $email) {
            $user = User::where('email', $email)->first();

            // Update provider_id to link the accounts (without activity logging)
            if ($user && $sub) {
                $user->disableLogging();
                $user->updateQuietly(['provider_id' => $sub, 'provider' => 'auth0']);
            }
        }

        return $user;
    }

    protected function getAuth0Sub(): ?string
    {
        $auth0User = Auth::guard('api')->user();

        return $auth0User->id ?? null;
    }
}
