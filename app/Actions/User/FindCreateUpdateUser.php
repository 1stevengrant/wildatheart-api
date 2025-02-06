<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class FindCreateUpdateUser
{
    use AsAction;

    public function handle(array $auth0User)
    {
        $userData = [
            'given_name' => $auth0User['given_name'] ?? null,
            'family_name' => $auth0User['family_name'] ?? null,
            'phone' => $auth0User['phone'] ?? null,
        ];

        $user = User::where('email', $auth0User['email'])->first();

        if ($user) {
            $hasChanges = false;

            foreach ($userData as $key => $value) {
                if ($user->{$key} !== $value) {
                    $hasChanges = true;
                    $user->{$key} = $value;
                }
            }

            if ($hasChanges) {
                $user->save();
            }

            return $user;
        }

        return User::create([
            'email' => $auth0User['email'],
            ...$userData,
            'password' => bcrypt(Str::random(32)),
            'provider_id' => $auth0User['sub'],
        ]);
    }
}
