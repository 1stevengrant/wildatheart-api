<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class LookupLatitudeLongitude implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Event $event) {}

    public function handle(): void
    {
        $street = rawurlencode($this->event->street);
        $city = rawurlencode($this->event->city);
        $state = rawurlencode($this->event->state);
        $zip = rawurlencode($this->event->zip);
        $country = rawurlencode('US');

        $response = Http::get(config('services.mapbox.api_url')."{$street}%20{$city}%20{$state}%20{$zip}%20${country}.json?access_token=".config('services.mapbox.api_key'));

        $body = json_decode($response->body());

        $coordinates = $body->features[0]->geometry->coordinates;

        $this->event->update([
            'latitude' => $coordinates[1],
            'longitude' => $coordinates[0],
        ]);

    }
}
