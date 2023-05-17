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

        $user = User::where(['email' => $userSocial->getEmail()])->first();

        if (! $user) {
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
        }

        if ($this->checkRegisteredEmail($user->email)) {
            $this->assignUserRoles($user);
        }

        auth()->login($user, true);

        if (session()->get('currentUrl') === route('attend')) {
            $user->assignRole(User::ROLE_ATTENDEE);

            return redirect(session()->get('currentUrl'));
        }

        if (session()->get('currentUrl') === route('host')) {
            $user->assignRole(User::ROLE_HOST);

            return redirect(session()->get('currentUrl'));
        }

        return redirect()->route('home');
    }

    public function checkRegisteredEmail(string $email)
    {
        $domain = substr(strrchr($email, '@'), 1);

        $whitelist = ['digitalevangelist.net', 'servant.io', 'wildatheart.org', 'ghijk.co.uk'];

        if (in_array($domain, $whitelist)) {
            return true;
        }

        return false;
    }

    public function assignUserRoles(User $user)
    {
        $user->assignRole(User::ROLE_ADMIN);
        $user->assignRole(User::ROLE_ATTENDEE);
        $user->assignRole(User::ROLE_HOST);
    }
}
