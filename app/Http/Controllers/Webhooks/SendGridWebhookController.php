<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\SlackAlerts\Facades\SlackAlert;

class SendGridWebhookController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $data = collect(request()->all());

        $data
            ->filter(function ($item) {
                return $item['event'] === 'dropped' || $item['event'] === 'bounce';
            })->each(function ($item) {
                $attendee = Attendee::where('email', $item['email'])->first();
                $user = User::where('email', $item['email'])->first();

                if ($attendee || $user) {
                    SlackAlert::to('events')
                        ->message('email to '.$item['email'].' '.$item['event']);
                }
            });

        return response()->json(['status' => 'ok']);

    }
}
