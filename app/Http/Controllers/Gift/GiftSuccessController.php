<?php

namespace App\Http\Controllers\Gift;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GiftSuccessController extends Controller
{
    public function __invoke(Request $request, Event $event)
    {
        return inertia('GiftSuccessPage', [
            'event' => $event,
        ]);
    }
}
