<?php

namespace App\Http\Controllers\User;

use Statamic\Facades\GlobalSet;
use App\Http\Controllers\Controller;

class HostAgreementPageController extends Controller
{
    public function index()
    {
        $hostAgreement = GlobalSet::findByHandle('local_platform');

        return inertia('HostAgreementPage', [
            'hostAgreement' => $hostAgreement->in('default')->get('host_agreement_content'),
        ]);
    }

    public function store()
    {
        auth()->user()->update(['agreed_to_host_agreement_date' => now()]);
        $redirectTo = session()->pull('host_agreement_redirect_to');

        if ($redirectTo) {
            return redirect($redirectTo);
        }

        return to_route('my.profile');
    }
}
