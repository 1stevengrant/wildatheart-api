<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Campfire;
use Creativeorange\Gravatar\Facades\Gravatar;

class CampfireController extends Controller
{
    public function __invoke()
    {
        return response()->json(
            [
                'type' => 'FeatureCollection',
                'features' => $this->getCampfires(),
            ],
        );
    }

    protected function getCampfires()
    {
        return cache()->remember('campfires_api', now()->addHour(), function () {
            return Campfire::query()->public()->get()->map(function ($campfire) {
                return [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $campfire->id,
                        'state' => getStateAbbreviation($campfire->state),
                        'city' => $campfire->city,
                        'attendee_limit' => $campfire->attendee_limit,
                        'spaces_remaining' => $campfire->remaining,
                        'url' => route('view.campfire', $campfire->id),
                        'host' => $campfire->user->full_name,
                        'hostProfilePhoto' => Gravatar::get($campfire->user->email, 'campfire-profile'),
                        'image' => url('img/campfire.png'),
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            $campfire->longitude,
                            $campfire->latitude,
                        ],
                    ],
                ];
            });
        });
    }
}
