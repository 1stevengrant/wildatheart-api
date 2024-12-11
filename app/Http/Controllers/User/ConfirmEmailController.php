<?php

namespace App\Http\Controllers\User;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConfirmEmailController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        return inertia('ConfirmEmailPage', [
            'event' => $request->event,
        ]);
    }
}
