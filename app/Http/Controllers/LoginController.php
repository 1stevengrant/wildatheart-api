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
        $currentUrl = session()->get('currentUrl');
        $campfireId = session()->get('campfireId') ?? 'abc';

        $userSocial = Socialite::driver($provider)->stateless()->user();

        $user = User::where(['email' => $userSocial->getEmail()])->first();

        if (! $user) {
            $user = User::create([
                'name' => $userSocial->getName() ?? null,
                'given_name' => $userSocial->user['given_name'] ?? null,
                'family_name' => $userSocial->user['family_name'] ?? null,
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

        if ($currentUrl === route('view.campfire', ['campfire' => $campfireId]) ||
            $currentUrl === route('list.campfires')) {
            $user->assignRole(User::ROLE_ATTENDEE);

            return redirect($currentUrl);
        }

        if ($currentUrl === route('host')) {
            $user->assignRole(User::ROLE_HOST);

            return redirect($currentUrl);
        }

        return redirect()->route('home');
    }

    public function checkRegisteredEmail(string $email)
    {
        $domain = substr(strrchr($email, '@'), 1);

        $whitelist = ['digitalevangelist.net', 'servant.io', 'wildatheart.org', 'ghijk.co.uk', 'ransomedheart.com'];

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
