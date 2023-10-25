<?php

namespace App\Http\Controllers\Gift;

use App\Http\Controllers\Controller;
use App\Models\Campfire;
use Illuminate\Http\Request;

class GiftSuccessController extends Controller
{
    public function __invoke(Request $request, Campfire $campfire)
    {
        return inertia('GiftSuccess', [
            'campfire' => $campfire->inertiaFilter(),
        ]);
    }
}
