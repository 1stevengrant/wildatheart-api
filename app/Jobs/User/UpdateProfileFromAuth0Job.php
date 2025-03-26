<?php

namespace App\Jobs\User;

use Log;
use App\Models\User;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Integrations\Auth0\Auth0Connector;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\Auth0\Requests\GetUserProfileRequest;

class UpdateProfileFromAuth0Job implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected User $user) {}

    public function handle(): void
    {
        $connector = new Auth0Connector;
        $getUserProfileRequest = new GetUserProfileRequest($this->user->provider_id);

        try {
            $response = $connector->send($getUserProfileRequest);
            $userMetadata = $response->json('user_metadata');

            if (! empty($userMetadata['unverified_phone_number'])) {
                $this->user?->update([
                    'phone' => $userMetadata['unverified_phone_number'],
                ]);
            }

        } catch (FatalRequestException|RequestException $e) {
            Log::error($e->getMessage());
        }
    }
}
