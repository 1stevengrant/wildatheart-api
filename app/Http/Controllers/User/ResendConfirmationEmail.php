<?php

namespace App\Http\Controllers\User;

use App\Actions\User\SendVerificationEmail;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class ResendConfirmationEmail extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Event $event)
    {
        SendVerificationEmail::run(event: $event);

        return response()->json([
            'message' => 'Confirmation email sent! Check your inbox.',
            'type' => 'success',
        ]);
    }
}
