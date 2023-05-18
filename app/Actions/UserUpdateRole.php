<?php

namespace App\Actions;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UserUpdateRole
{
    use AsAction;

    public function handle(User $user, string $role): void
    {
        $user->assignRole($role);
    }
}
