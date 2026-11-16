<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;
use App\Enums\UserRoleEnum;

class EventPolicy
{
    public function update(User $user, Event $event): bool
    {
        return $event->isOwnedOrManagedBy($user)
            || $user->hasRole(UserRoleEnum::Admin);
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRoleEnum::Admin);
    }

    public function view(User $user, Event $event): bool
    {
        return $event->isOwnedOrManagedBy($user)
            || $user->hasRole(UserRoleEnum::Admin);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRoleEnum::Admin);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->id === $event->user_id
            || $user->hasRole(UserRoleEnum::Admin);
    }

    public function manage(User $user, Event $event): bool
    {
        return $event->isOwnedOrManagedBy($user)
            || $user->hasRole(UserRoleEnum::Admin);
    }

    public function requestGift(User $user, Event $event): bool
    {
        return $user->id === $event->user_id;
    }
}
