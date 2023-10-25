<?php

namespace App\Http\Controllers\Gift;

use App\Http\Controllers\Controller;
use App\Models\Campfire;
use Illuminate\Http\Request;

class SendGiftController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Campfire $campfire)
    {
        if ($campfire->hasRequestedGift()) {
            abort(403, 'You have already requested your gift.');
        }

        return inertia('SendGift', [
            'campfire' => $campfire,
        ]);
    }
}
