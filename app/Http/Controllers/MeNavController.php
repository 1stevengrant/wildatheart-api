<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Data\ProfileMenuData;
use App\Data\ProfileMenuItemData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Concerns\ResolvesAuthenticatedUser;

class MeNavController extends Controller
{
    use ResolvesAuthenticatedUser;

    private const string AUTH0_AUTHORIZE_URL = 'https://auth.wildatheart.org/authorize?&scope=openid%20profile%20email&response_type=token&client_id=fO67RxA7uhoqalomshKysUv04aSoP6WK';

    public function __invoke(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (! $user) {
            return response()->json($this->getGuestMenu());
        }

        if ($user->hasRole(User::ROLE_HOST)) {
            return response()->json($this->getHostMenu());
        }

        return response()->json($this->getAttendeeMenu());
    }

    private function getGuestMenu(): ProfileMenuData
    {
        return new ProfileMenuData(
            urlToProfile: self::AUTH0_AUTHORIZE_URL . '&redirect_uri=https://my.wildatheart.org/wah/settings?tab=2',
            primaryItems: [],
            secondaryItems: [],
        );
    }

    private function getAttendeeMenu(): ProfileMenuData
    {
        return new ProfileMenuData(
            urlToProfile: self::AUTH0_AUTHORIZE_URL . '&redirect_uri=https://my.wildatheart.org/wah/settings?tab=2',
            primaryItems: [
                new ProfileMenuItemData(
                    label: 'My Giving',
                    href: self::AUTH0_AUTHORIZE_URL . '&redirect_uri=https://my.wildatheart.org/wah/settings?tab=1',
                    iconName: 'heart',
                ),
            ],
            secondaryItems: [
                new ProfileMenuItemData(
                    label: 'Host an Event',
                    href: 'https://local.wildatheart.org/host/select',
                ),
            ],
        );
    }

    private function getHostMenu(): ProfileMenuData
    {
        return new ProfileMenuData(
            urlToProfile: self::AUTH0_AUTHORIZE_URL . '&redirect_uri=https://my.wildatheart.org/wah/settings?tab=2',
            primaryItems: [
                new ProfileMenuItemData(
                    label: 'My Giving',
                    href: self::AUTH0_AUTHORIZE_URL . '&redirect_uri=https://my.wildatheart.org/wah/settings?tab=1',
                    iconName: 'heart',
                ),
                new ProfileMenuItemData(
                    label: 'My Events',
                    href: 'https://local.wildatheart.org/events/active',
                    iconName: 'bookSquare',
                ),
            ],
            secondaryItems: [
                new ProfileMenuItemData(
                    label: 'Host an Event',
                    href: 'https://local.wildatheart.org/host/select',
                ),
            ],
        );
    }
}
