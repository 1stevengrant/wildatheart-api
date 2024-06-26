<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Statamic\Facades\GlobalSet;

class HostAgreementPageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $hostAgreement = GlobalSet::findByHandle('local_platform');
        $hostAgreement = $hostAgreement->in('default')->get('host_agreement');

        return inertia('HostAgreementPage', [
            'hostAgreement' => $hostAgreement,
        ]);
    }
}
