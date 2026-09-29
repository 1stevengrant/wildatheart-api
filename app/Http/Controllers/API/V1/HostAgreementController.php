<?php

namespace App\Http\Controllers\API\V1;

use App\Data\UserData;
use Illuminate\Http\JsonResponse;
use App\Data\V1\HostAgreementData;
use App\Http\Controllers\Controller;

class HostAgreementController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(HostAgreementData::fromGlobals());
    }

    public function store(): JsonResponse
    {
        $user = $this->user();

        $user->update(['agreed_to_host_agreement_date' => now()]);

        return response()->json(UserData::fromModel($user->fresh()));
    }
}
