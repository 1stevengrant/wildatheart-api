<?php

namespace App\Http\Controllers;

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
