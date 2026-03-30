<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class FindOrCreateUser
{
    use AsAction;

    public function handle(string $email, array $attributes = []): User
    {
        return User::firstOrCreate(
            ['email' => mb_strtolower($email)],
            [
                ...$attributes,
                'password' => bcrypt(Str::random(32)),
            ]
        );
    }
}
