<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\Request;

class ResourcesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return inertia('ResourcesPage', [
            'eventTypes' => EventType::query()->active()->orderBy('order')->get(),
        ]);
    }
}
