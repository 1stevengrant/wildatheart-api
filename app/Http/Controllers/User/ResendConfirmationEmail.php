<?php

namespace App\Http\Controllers\User;

use App\Actions\User\SendVerificationEmail;
use App\Http\Controllers\Controller;
use App\Models\Campfire;
use Illuminate\Http\Request;

class ResendConfirmationEmail extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Campfire $campfire)
    {
        SendVerificationEmail::run(campfire: $campfire);

        return response()->json([
            'message' => 'Confirmation email sent! Check your inbox.',
            'type' => 'success',
        ]);
    }
}
