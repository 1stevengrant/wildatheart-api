<?php

namespace App\Actions\User;

use App\Models\User;
use App\Jobs\LogActivityJob;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUserRole
{
    use AsAction;

    public function handle(User $user, string $role): void
    {
        $user->assignRole($role);
        dispatch(new LogActivityJob(
            description: 'Update User Role',
            causedBy: auth()->user(),
            performedOn: $user
        ));
    }
}
