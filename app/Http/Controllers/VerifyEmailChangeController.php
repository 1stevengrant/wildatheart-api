<?php

namespace App\Http\Controllers;

use App\Jobs\Auth0\UpdateUserJob;
use App\Jobs\WeGive\UpdateDonorEmailJob;

class VerifyEmailChangeController extends Controller
{
    public function __invoke($token)
    {

        $user = \App\Models\User::byVerificationToken($token)->firstOrFail();

        if (! $user || empty($user->pending_email)) {
            return redirect()->route('my.profile')->with([
                'notifications' => [
                    'message' => 'Invalid verification link or email already verified.',
                    'type' => 'error',
                ],
            ]);
        }

        $user->update([
            'email' => $user->pending_email,
            'email_verified_at' => now(),
            'pending_email' => null,
            'email_verification_token' => null,
        ]);

        if ($user->wegive_donor_id) {
            UpdateDonorEmailJob::dispatch($user->wegive_donor_id, $user->email);
        }

        if ($user->provider_id) {
            UpdateUserJob::dispatch($user->provider_id, $user->email, true);
        }

        return redirect()->route('my.profile')->with([
            'notifications' => [
                'message' => 'Your email has been updated successfully!',
                'type' => 'success',
            ],
        ]);
    }
}
