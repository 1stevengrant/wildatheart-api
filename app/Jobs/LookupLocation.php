<?php

namespace App\Jobs;

use App\Models\Event;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LookupLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Event $event) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $location = $this->lookup($this->event->latitude, $this->event->longitude);

        $country = collect($location)->firstWhere(fn ($feature) => str_contains($feature->id, 'country'))->text ?? null;
        $region = collect($location)->firstWhere(fn ($feature) => str_contains($feature->id, 'region'))->text ?? null;
        $postcode = collect($location)->firstWhere(fn ($feature) => str_contains($feature->id, 'postcode'))->text ?? null;
        $district = collect($location)->firstWhere(fn ($feature) => str_contains($feature->id, 'district'))->text ?? null;
        $place = collect($location)->firstWhere(fn ($feature) => str_contains($feature->id, 'place'))->text ?? null;
        $locality = collect($location)->firstWhere(fn ($feature) => str_contains($feature->id, 'locality'))->text ?? null;
        $neighborhood = collect($location)->firstWhere(fn ($feature) => str_contains($feature->id, 'neighborhood'))->text ?? null;
        $streetAddress = collect($location)->firstWhere(fn ($feature) => str_contains($feature->id, 'address'))->text ?? null;

        try {
            $this->event->update([
                'country' => $country ? getCountryCodeFromCountryName($country) : null,
                'region' => $region,
                'zip' => $postcode,
                'district' => $district,
                'place' => $place,
                'locality' => $locality,
                'neighborhood' => $neighborhood,
                'street_address' => $streetAddress,
                'city' => $place,
                'state' => $region,
            ]);

        } catch (Exception $e) {
            Log::error("$this->event->name has been a problem with your fetch operation: {$e->getMessage()}");
        }
    }

    public function lookup($latitude, $longitude)
    {
        try {
            $response = Http::get("https://api.mapbox.com/geocoding/v5/mapbox.places/{$longitude},{$latitude}.json", [
                'access_token' => config('services.mapbox.api_key'),
            ]);

            if ($response->failed()) {
                throw new Exception('Network response was not ok');
            }

            return json_decode($response->body())->features;
        } catch (Exception $e) {
            Log::error("There has been a problem with your fetch operation: {$e->getMessage()}");

            return null;
        }
    }
}
