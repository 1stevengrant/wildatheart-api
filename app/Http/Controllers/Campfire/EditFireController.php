<?php

namespace App\Http\Controllers\Campfire;

use App\Http\Controllers\Controller;
use App\Models\Campfire;
use Illuminate\Http\Request;

class EditFireController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Campfire $campfire)
    {
        if ($campfire->host->id !== auth()->id()) {
            return redirect()->route('home');
        }

        return inertia('Profile/EditFire', [
            'campfire' => $campfire->inertiaFilter(),
        ]);
    }
}
