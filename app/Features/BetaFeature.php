<?php

namespace App\Features;

use App\Models\User;
use App\Enums\UserRoleEnum;

class BetaFeature
{
    public function resolve(?User $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        return $user->hasRole(UserRoleEnum::BetaTester->value)
            || $user->beta_enabled === true;
    }
}
