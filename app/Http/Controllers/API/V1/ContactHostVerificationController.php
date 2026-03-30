<?php

namespace App\Http\Controllers\API\V1;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use App\Mail\Event\ContactHostVerificationMail;

class ContactHostVerificationController extends Controller
{
    public function store(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255'],
        ]);

        $token = Crypt::encryptString(json_encode([
            'email' => mb_strtolower($request->email),
            'event_id' => $event->id,
            'expires_at' => now()->addHour()->toISOString(),
        ]));

        $frontendUrl = mb_rtrim(config('app.frontend_url', config('app.url')), '/');
        $email = urlencode(mb_strtolower($request->email));
        $verificationUrl = "{$frontendUrl}/events/attend/{$event->id}?contact_token={$token}&email={$email}";

        Mail::to($request->email)->send(
            new ContactHostVerificationMail($event, $verificationUrl)
        );

        return response()->json([
            'message' => 'If this email is valid, you will receive a verification link shortly. Please check your inbox.',
        ]);
    }
}
