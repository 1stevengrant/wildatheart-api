<?php

namespace App\Actions\User;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUserRole
{
    use AsAction;

    public function handle(User $user, string $role): void
    {
        $user->assignRole($role);
    }
}
