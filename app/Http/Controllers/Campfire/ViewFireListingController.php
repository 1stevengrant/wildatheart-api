<?php

namespace App\Http\Controllers\Campfire;

use App\Http\Controllers\Controller;
use App\Models\Campfire;
use Illuminate\Http\Request;

class ViewFireListingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Campfire $campfire)
    {
        if ($campfire->user_id != optional(auth()->user())->id && ! $campfire->approved_at) {
            abort(404);
        }

        return inertia('ViewFire', [
            'campfire' => $campfire->inertiaFilter(),
        ]);
    }
}
