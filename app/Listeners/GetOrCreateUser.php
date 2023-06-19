<?php

namespace App\Listeners;

use App\Models\User;

class GetOrCreateUser
{
    public function handle(object $event): void
    {
        $userObject = $event->user;
        $user = User::firstOrCreate([
            'email' => $userObject->email,
        ]);

        $user->update([
            'last_login_date' => now(),
            'given_name' => $userObject->given_name,
            'family_name' => $userObject->family_name,
            'name' => $userObject->name,
            'picture' => $userObject->picture,
            'locale' => $userObject->locale,
            'email_verified' => $userObject->email_verified,
            'iss' => $userObject->iss,
            'aud' => $userObject->aud,
            'iat' => $userObject->iat,
            'exp' => $userObject->exp,
            'sub' => $userObject->sub,
            'sid' => $userObject->sid,
            'nonce' => $userObject->nonce,
        ]);
    }
}
