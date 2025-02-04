<?php

namespace App\Services\Auth0;

use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class OAuthTokenService
{
    use AsAction;

    public function handle()
    {
        $response = Http::asForm()
            ->post(config('auth0.management.endpoint'), [
                'grant_type' => 'client_credentials',
                'client_id' => config('auth0.management.client_id'),
                'client_secret' => config('auth0.management.client_secret'),
                'audience' => config('auth0.management.audience'),
            ]);

        return $response->json();
    }
}
