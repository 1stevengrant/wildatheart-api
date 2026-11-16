<?php

namespace App\Http\Controllers\API\V1;

use App\Models\User;
use App\Data\MailingAddressData;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\MailingAddressRequest;
use Illuminate\Container\Attributes\CurrentUser;
use App\Actions\Address\SetPrimaryMailingAddress;

class HostMailingAddressController extends Controller
{
    public function store(MailingAddressRequest $request, #[CurrentUser] User $user): JsonResponse
    {
        $address = SetPrimaryMailingAddress::run($user, MailingAddressData::from($request->validated()));

        return response()->json([
            'success' => true,
            'address' => $address,
        ]);
    }
}
