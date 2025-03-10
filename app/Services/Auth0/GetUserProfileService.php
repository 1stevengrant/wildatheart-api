<?php

namespace App\Services\Auth0;

use Illuminate\Support\Facades\Http;
use Lorisleiva\Actions\Concerns\AsAction;

class GetUserProfileService
{
    use AsAction;

    public function handle(string $accessToken, string $userId)
    {
        return Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ])
            ->get(config('auth0.management.audience') . 'users/' . $userId)
            ->json();
    }
}
