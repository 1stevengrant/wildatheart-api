<?php

namespace App\Http\Controllers;

use App\Actions\Address\CreateAddress;
use App\Actions\Campfire\UpdateCampfire;
use App\Jobs\PostToZapier;
use App\Mail\Campfire\GiftRequested;
use App\Models\Campfire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class HandleSendGiftController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Campfire $campfire)
    {
        $request->validate([
            'country' => 'required|max:255',
            'given_name' => 'required|max:255|min:2',
            'family_name' => 'required|max:255|min:2',
            'company' => 'nullable',
            'street_address' => 'required|max:255',
            'address2' => 'nullable',
            'city' => 'required|max:255',
            'state' => ['required'],
            'zip' => 'required|max:20|min:4',
            'phone' => 'required',
        ]);

        CreateAddress::run([
            'given_name' => request('given_name'),
            'family_name' => request('family_name'),
            'company' => request('company'),
            'street_address' => request('street_address'),
            'address2' => request('address2'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'phone' => request('phone'),
            'is_primary' => true,
            'country' => request('country'),
            'campfire_id' => $campfire->id,
        ]);

        UpdateCampfire::run(
            data: ['gift_requested' => true],
            campfire: $campfire
        );

        Mail::to($campfire->host->email)
            ->send(new GiftRequested($campfire));

        PostToZapier::dispatch(data: [
            'email' => $campfire->host->email,
            'given_name' => request('given_name'),
            'family_name' => request('family_name'),
            'street_address' => request('street_address'),
            'address_2' => request('address2'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'country' => request('country'),
        ]);

        return to_route('gift.success', $campfire);
    }
}
