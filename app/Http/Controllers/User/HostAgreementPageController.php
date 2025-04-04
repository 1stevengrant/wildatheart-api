<?php

namespace App\Http\Controllers\User;

use Statamic\Facades\GlobalSet;
use App\Http\Controllers\Controller;

class HostAgreementPageController extends Controller
{
    public function index()
    {
        $hostAgreement = GlobalSet::findByHandle('local_platform');
        $hostAgreement = $hostAgreement->in('default')->get('host_agreement_content');

        return inertia('HostAgreementPage', [
            'hostAgreement' => $hostAgreement,
        ]);
    }

    public function store()
    {
        auth()->user()->update(['agreed_to_host_agreement_date' => now()]);
        $redirectTo = session()->pull('host_agreement_redirect_to');
        if ($redirectTo) {
            return redirect()->route($redirectTo);
        }

        return redirect()->route('my.profile');
    }
}
