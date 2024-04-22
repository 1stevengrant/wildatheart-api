<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;

class AgreeToHostAgreementController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        ray('AgreeToHostAgreementController', auth()->user());
        auth()->user()->update(['agreed_to_host_agreement_date' => now()]);

        return redirect()->route('host.eventType.index', ['eventType' => EventType::first()->slug]);
    }
}
