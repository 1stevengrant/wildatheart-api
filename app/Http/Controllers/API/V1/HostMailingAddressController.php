<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailingAddressRequest;

class HostMailingAddressController extends Controller
{
    public function store(MailingAddressRequest $request)
    {
        $user = auth()->user();

        $user->addresses()->update([
            'is_primary' => false,
        ]);

        if (empty($user->family_name) || empty($user->given_name)) {
            $user->update([
                'given_name' => $request->given_name,
                'family_name' => $request->family_name,
                'name' => $request->given_name.' '.$request->family_name,
            ]);
        }

        $address = $user->addresses()->create([
            'user_id' => $user->id,
            'given_name' => $request->given_name,
            'family_name' => $request->family_name,
            'street_address' => $request->street_address,
            'address2' => $request->address2,
            'company' => $request->company,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->zip,
            'country' => $request->country,
            'phone' => $request->phone,
            'is_primary' => true,
        ]);

        return response()->json([
            'success' => true,
            'address' => $address,
        ]);
    }
}
