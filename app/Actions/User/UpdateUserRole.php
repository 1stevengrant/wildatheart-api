<?php

namespace App\Actions\User;

use App\Models\User;
use App\Enums\UserRoleEnum;
use App\Jobs\LogActivityJob;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUserRole
{
    use AsAction;

    public function handle(User $user, UserRoleEnum $role): void
    {
        $user->assignRole($role->value);
        LogActivityJob::dispatch(
            description: 'Update User Role',
            causedBy: auth()->user(),
            performedOn: $user
        );
    }
}
