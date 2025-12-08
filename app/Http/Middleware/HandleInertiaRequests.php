<?php

namespace App\Http\Middleware;

use Inertia\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

// use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth.user' => fn () => $request->user()
                ? $this->transformUser($request->user())
                : null,
            'ziggy' => fn () => [
                //                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'notifications' => fn () => Session::get('notifications'),
        ];
    }

    protected function transformUser($user): array
    {
        return array_merge($user->only(
            'id',
            'given_name',
            'family_name',
            'fullname',
            'email',
            'phone',
            'opted_into_marketing',
            'profile_step'
        ), [
            'image' => $user->profilePhoto(),
            'has_complete_profile' => $user->hasCompleteProfile(),
        ]);
    }
}
