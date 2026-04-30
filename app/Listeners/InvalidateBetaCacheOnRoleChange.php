<?php

namespace App\Listeners;

use Throwable;
use App\Models\User;
use App\Enums\UserRoleEnum;
use Laravel\Pennant\Feature;
use App\Features\BetaFeature;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class InvalidateBetaCacheOnRoleChange
{
    public function handle(RoleAttached|RoleDetached $event): void
    {
        if (! $event->model instanceof User) {
            return;
        }

        if (! $this->affectsBetaTester($event->rolesOrIds)) {
            return;
        }

        try {
            Feature::for($event->model)->forget(BetaFeature::class);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function affectsBetaTester(mixed $rolesOrIds): bool
    {
        $candidates = is_iterable($rolesOrIds) ? $rolesOrIds : [$rolesOrIds];
        $betaName = UserRoleEnum::BetaTester->value;

        $betaId = false;

        foreach ($candidates as $candidate) {
            if ($candidate instanceof Role && $candidate->name === $betaName) {
                return true;
            }

            if (is_string($candidate) && $candidate === $betaName) {
                return true;
            }

            if (is_int($candidate)) {
                if ($betaId === false) {
                    try {
                        $betaId = Role::findByName($betaName)->getKey();
                    } catch (RoleDoesNotExist) {
                        $betaId = null;
                    }
                }
                if ($betaId !== null && $candidate === $betaId) {
                    return true;
                }
            }
        }

        return false;
    }
}
