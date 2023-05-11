<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $userSocial = Socialite::driver($provider)->stateless()->user();

        $users = User::where(['email' => $userSocial->getEmail()])->first();
        if ($users) {
            auth()->login($users);

            return redirect('/');
        } else {
            $user = User::create([
                'name' => $userSocial->getName(),
                'given_name' => $userSocial->user['given_name'],
                'family_name' => $userSocial->user['family_name'],
                'email_verified_at' => $userSocial->user['email_verified'] ? now() : null,
                'email' => $userSocial->getEmail(),
                'image' => $userSocial->user['picture'],
                'provider_id' => $userSocial->getId(),
                'provider' => $provider,
            ]);

            auth()->login($user, true);

            return redirect()->route('dashboard');
        }
    }
}
