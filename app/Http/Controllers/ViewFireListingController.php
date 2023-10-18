<?php

namespace App\Http\Controllers;

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
