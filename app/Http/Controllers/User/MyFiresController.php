<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class MyFiresController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $fires = auth()->user()->campfires()->with('host')
            ->paginate()
            ->through(fn ($fire) => [
                'id' => $fire->id,
                'name' => $fire->name,
                'phone' => $fire->phone,
                'frequency' => $fire->frequency,
                'street_address' => $fire->street_address,
                'state' => $fire->state,
                'state_code' => $fire->state_code,
                'city' => $fire->city,
                'zip' => $fire->zip,
                'country' => $fire->country,
                'longitude' => $fire->longitude,
                'latitude' => $fire->latitude,
                'region' => $fire->region,
                'district' => $fire->district,
                'locality' => $fire->locality,
                'location' => $fire->location,
                'neighborhood' => $fire->neighborhood,
                'place' => $fire->place,
                'gift_requested' => $fire->gift_requested,
                'gift_sent' => $fire->gift_sent,
                'host' => $fire->host->full_name,
            ]);

        return inertia('Profile/MyFires', [
            'campfires' => $fires,
        ]);
    }
}
