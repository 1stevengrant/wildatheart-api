<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;

class EventPolicy
{
    public function update(User $user, Event $event): bool
    {
        return $event->isOwnedOrManagedBy($user)
            || $user->hasRole(User::ROLE_ADMIN);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
    }

    public function view(User $user, Event $event): bool
    {
        return $event->isOwnedOrManagedBy($user)
            || $user->hasRole(User::ROLE_ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(User::ROLE_ADMIN);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->id === $event->user_id
            || $user->hasRole(User::ROLE_ADMIN);
    }

    public function manage(User $user, Event $event): bool
    {
        return $event->isOwnedOrManagedBy($user)
            || $user->hasRole(User::ROLE_ADMIN);
    }
}
