<?php

namespace App\Http\Controllers\Campfire;

use App\Actions\Campfire\ApproveCampfire;
use App\Actions\Campfire\CheckWelcomeStatus;
use App\Actions\User\SendVerificationEmail;
use App\Actions\User\UpdateUserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\ShouldNotEndWithFire;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Repat\LaravelRules\DoesntContainEmojis;

class CreateCampfireController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                new ShouldNotEndWithFire,
                new DoesntContainEmojis,
            ],
            'country' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $campfire = auth()->user()->campfires()->create([
            'name' => request('name'),
            'street_address' => request('street_address'),
            'state' => request('state'),
            'city' => request('city'),
            'zip' => request('zip'),
            'country' => getCountryCodeFromCountryName(request('country')),
            'latitude' => request('latitude'),
            'longitude' => request('longitude'),
            'region' => request('region'),
            'district' => request('district'),
            'locality' => request('locality'),
            'neighborhood' => request('neighborhood'),
        ]);

        UpdateUserRole::make()->handle(
            user: auth()->user(),
            role: User::ROLE_HOST
        );

        if (! auth()->user()->hasVerifiedEmail()) {
            SendVerificationEmail::make()->handle(campfire: $campfire);

            return Inertia::location(to_route('confirm.email', ['campfire' => $campfire]));
        }

        ApproveCampfire::run(campfire: $campfire);

        CheckWelcomeStatus::run(campfire: $campfire);

        return Inertia::location(to_route('gift', ['campfire' => $campfire]));
    }
}
