<?php

namespace App\Jobs;

use App\Models\Campfire;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class LookupLatitudeLongitude implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Campfire $campFire)
    {

    }

    public function handle(): void
    {
        $street = rawurlencode($this->campFire->street);
        $city = rawurlencode($this->campFire->city);
        $state = rawurlencode($this->campFire->state);
        $zip = rawurlencode($this->campFire->zip);
        $country = rawurlencode('US');

        $response = Http::get(config('services.mapbox.api_url')."{$street}%20{$city}%20{$state}%20{$zip}%20${country}.json?access_token=".config('services.mapbox.api_key'));

        $body = json_decode($response->body());

        $coordinates = $body->features[0]->geometry->coordinates;

        $this->campFire->update([
            'latitude' => $coordinates[1],
            'longitude' => $coordinates[0],
        ]);

    }
}
