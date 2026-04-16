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
    protected Auth0SDK $auth0;

    public function __construct(
        Auth0SDK $auth0
    ) {
        $this->auth0 = $auth0;
    }

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

            // Check if any ownership transfers or host invitations were activated
            $message = 'You are now logged in!';

            // Ownership transfers take priority in the message
            if (! empty($activatedOwnershipTransfers)) {
                $firstTransfer = $activatedOwnershipTransfers[0];
                $eventName = $firstTransfer['event_name'];

                if (count($activatedOwnershipTransfers) === 1) {
                    $message = "You are now the owner of {$eventName}!";
                } else {
                    $message = "You are now the owner of {$eventName} and " . (count($activatedOwnershipTransfers) - 1) . ' other event(s)!';
                }

                LogActivityJob::dispatch(
                    logName: 'auth',
                    description: 'Ownership transfers activated',
                    properties: [
                        'count' => count($activatedOwnershipTransfers),
                        'message' => $message,
                    ],
                );

                // Clear the activated transfers from session
                Session::forget('activated_ownership_transfers');
            } elseif (! empty($activatedInvitations)) {
                // Get the first activated invitation for the message
                $firstInvitation = $activatedInvitations[0];
                $eventName = $firstInvitation['event_name'];

                if (count($activatedInvitations) === 1) {
                    $message = "You are now a host for {$eventName}!";
                } else {
                    $message = "You are now a host for {$eventName} and " . (count($activatedInvitations) - 1) . ' other event(s)!';
                }

                LogActivityJob::dispatch(
                    logName: 'auth',
                    description: 'Host invitations activated',
                    properties: [
                        'count' => count($activatedInvitations),
                        'message' => $message,
                    ],
                );

                // Clear the activated invitations from session
                Session::forget('activated_invitations');
            }

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

    protected function findOrCreateUser(array $auth0User): User
    {
        $user = User::withTrashed()->byEmail($auth0User['email'])->first();

        if ($user === null) {
            $user = User::create([
                'email' => $auth0User['email'],
                'given_name' => $auth0User['given_name'] ?? null,
                'family_name' => $auth0User['family_name'] ?? null,
                'password' => bcrypt(Str::random(32)),
                'provider_id' => $auth0User['sub'],
            ]);

            LogActivityJob::dispatch(
                logName: 'auth',
                description: 'Created user',
                properties: ['user' => $user->toArray()],
            );

            return $user;
        }

        if ($user->trashed()) {
            $user->restore();
            $user->forceFill(['provider_id' => $auth0User['sub']])->save();

            LogActivityJob::dispatch(
                logName: 'auth',
                description: 'Restored soft-deleted user on re-signup',
                properties: ['user' => $user->toArray()],
            );
        }

        return $user;
    }
}
