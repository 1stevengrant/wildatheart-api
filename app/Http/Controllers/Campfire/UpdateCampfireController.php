<?php

namespace App\Http\Controllers\Campfire;

use App\Http\Controllers\Controller;
use App\Models\Campfire;
use App\Rules\ShouldNotEndWithFire;
use Illuminate\Http\Request;
use Repat\LaravelRules\DoesntContainEmojis;

class UpdateCampfireController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Campfire $campfire)
    {
        if ($campfire->user_id != auth()->user()->id) {
            abort(403);
        }

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

        $campfire->update($request->all());

        return to_route('my.campfires')->with(['notifications' => [
            'message' => 'Fire updated successfully!',
            'type' => 'success',
        ],
        ]);
    }
}
