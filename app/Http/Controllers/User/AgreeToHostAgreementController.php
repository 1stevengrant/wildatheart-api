<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AgreeToHostAgreementController extends Controller
{
    public function __invoke(Request $request)
    {
        auth()->user()->update(['agreed_to_host_agreement_date' => now()]);
        $redirectTo = session()->pull('host_agreement_redirect_to');
        if ($redirectTo) {
            return redirect()->route($redirectTo);
        }

        return redirect()->route('my.profile');

    }
}
