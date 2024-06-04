<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgreeToHostAgreementController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        auth()->user()->update(['agreed_to_host_agreement_date' => now()]);

        $redirectTo = session()->pull('host_agreement_redirect_to');

        return redirect()->route($redirectTo);
    }
}
