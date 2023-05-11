<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserService
{
    public function getUserByEmail(string $email): Model
    {
        return User::query()->where('email', '=', $email)->first();
    }

    public function makeUserHost(string $email): void
    {
        ray($email);
        $user = User::query()->where('email', '=', $email)->first();
        $user->assignRole('host');
    }

    public function makeUserAttendee(string $email): void
    {
        ray($email);
        $user = User::query()->where('email', '=', $email)->first();
        $user->assignRole('attendee');
    }
}
