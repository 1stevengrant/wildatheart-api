<?php

namespace App\Builders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AttendeeBuilder extends Builder
{
    public function controllableByUser(User $user): AttendeeBuilder
    {
        return $this->whereHas('event', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('managers', fn ($q) => $q->where('user_id', $user->id));
        });
    }

    public function orderByName(): AttendeeBuilder
    {
        return $this->orderBy('family_name')->orderBy('given_name');
    }
}
