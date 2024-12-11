<?php

namespace App\Http\Controllers\Gift;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SendGiftController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        abort_unless(! $event->hasRequestedGift(), 403, 'You have already requested your gift.');

        return inertia('SendGiftPage', [
            'event' => $event,
        ]);
    }
}
