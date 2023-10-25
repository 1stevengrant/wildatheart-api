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
        return inertia('ViewFire', [
            'campfire' => $campfire->inertiaFilter(),
        ]);
    }
}
