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
        $user = User::query()->where('email', '=', $email)->first();
        $user->became_host = now();
        $user->save();
    }
}
