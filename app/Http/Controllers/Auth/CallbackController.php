<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use App\Jobs\LogActivityJob;
use Illuminate\Http\Request;
use Auth0\SDK\Auth0 as Auth0SDK;
use App\Http\Controllers\Controller;
use Auth0\SDK\Exception\StateException;
use Illuminate\Support\Facades\Session;
use App\Jobs\User\UpdateProfileFromAuth0Job;

class CallbackController extends Controller
{
    public function __construct(
        protected Auth0SDK $auth0
    ) {}

    public function __invoke(Request $request)
    {
        try {
            $response = $this->auth0->exchange();
            abort_unless($response, 401, LogActivityJob::dispatch(
                logName: 'auth',
                description: 'Unauthorized access attempt',
            ));

            $auth0User = $this->auth0->getUser();

            $user = $this->findOrCreateUser($auth0User);

            UpdateProfileFromAuth0Job::dispatch($user);

            auth()->login($user);

            LogActivityJob::dispatch(
                logName: 'auth',
                description: 'user logged in',
                properties: [
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                    ],
                    'auth0User' => $auth0User,
                ],
            );

            $intendedUrl = Session::get('auth.intended_url');
            $activatedInvitations = Session::get('activated_invitations', []);
            $activatedOwnershipTransfers = Session::get('activated_ownership_transfers', []);

            LogActivityJob::dispatch(
                logName: 'auth',
                description: 'Checking host invitation and ownership transfers',
                properties: [
                    'intendedUrl' => $intendedUrl,
                    'activatedInvitations' => $activatedInvitations,
                    'activatedOwnershipTransfers' => $activatedOwnershipTransfers,
                ],
            );

            $message = $this->buildActivationMessage($activatedOwnershipTransfers, $activatedInvitations);

            // Clear the intended URL session data
            Session::forget('auth.intended_url');
            Session::forget('auth.invitation_id');
            Session::forget('auth.ownership_transfer_id');

            // Fallback to home if no intended URL
            $redirectUrl = $intendedUrl ?: route('home');

            return redirect($redirectUrl)->with([
                'notifications' => [
                    'message' => $message,
                    'type' => 'success',
                ],
            ]);

        } catch (StateException $e) {
            LogActivityJob::dispatch(
                logName: 'auth',
                description: 'Unauthorized access attempt',
                properties: ['exception' => $e],
            );

            return to_route('login');
        }
    }

    protected function buildActivationMessage(array $transfers, array $invitations): string
    {
        if (! empty($transfers)) {
            $message = $this->formatActivationMessage($transfers, 'the owner of');

            LogActivityJob::dispatch(
                logName: 'auth',
                description: 'Ownership transfers activated',
                properties: ['count' => count($transfers), 'message' => $message],
            );

            Session::forget('activated_ownership_transfers');

            return $message;
        }

        if (! empty($invitations)) {
            $message = $this->formatActivationMessage($invitations, 'a host for');

            LogActivityJob::dispatch(
                logName: 'auth',
                description: 'Host invitations activated',
                properties: ['count' => count($invitations), 'message' => $message],
            );

            Session::forget('activated_invitations');

            return $message;
        }

        return 'You are now logged in!';
    }

    protected function formatActivationMessage(array $items, string $role): string
    {
        $eventName = $items[0]['event_name'];

        if (count($items) === 1) {
            return "You are now {$role} {$eventName}!";
        }

        return "You are now {$role} {$eventName} and " . (count($items) - 1) . ' other event(s)!';
    }

    protected function findOrCreateUser(array $auth0User): User
    {
        $user = User::firstOrCreate(
            ['email' => $auth0User['email']],
            [
                'given_name' => $auth0User['given_name'] ?? null,
                'family_name' => $auth0User['family_name'] ?? null,
                'password' => bcrypt(Str::random(32)),
                'provider_id' => $auth0User['sub'],
            ]
        );

        LogActivityJob::dispatch(
            logName: 'auth',
            description: 'Created user',
            properties: ['user' => $user->toArray()],
        );

        return $user;
    }
}
