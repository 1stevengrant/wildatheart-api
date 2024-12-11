<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Statamic\Facades\GlobalSet;
use App\Http\Controllers\Controller;

class HostAgreementPageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $hostAgreement = GlobalSet::findByHandle('local_platform');
        $hostAgreement = $hostAgreement->in('default')->get('host_agreement_content');

        return inertia('HostAgreementPage', [
            'hostAgreement' => $hostAgreement,
        ]);
    }
}
