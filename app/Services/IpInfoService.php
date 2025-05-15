<?php

namespace App\Services;

use App\Models\IpLookup;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class IpInfoService
{
    private PendingRequest $client;

    private string $baseUrl = 'https://ipinfo.io';

    private int $refreshThresholdDays;

    public function __construct()
    {
        $token = config('services.ipinfo.token');
        $this->refreshThresholdDays = config('services.ipinfo.refresh_threshold_days', 30);

        $this->client = Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$token}",
            ])
            ->timeout(5)
            ->retry(2, 100);
    }

    /**
     * Get IP information with database caching
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getIpDetails(?string $ip = null): array
    {
        $ip = $ip ?: request()->ip();

        // Check database first
        $lookup = IpLookup::where('ip', $ip)->first();

        // If we have a recent lookup, return it
        if ($lookup && $this->isLookupValid($lookup)) {
            return $lookup->raw_data;
        }

        // If we need to fetch new data
        $response = $this->client->get($ip ? "/{$ip}/json" : '/json')->throw()->json();

        // Parse location
        $location = null;
        if (! empty($response['loc'])) {
            [$lat, $lng] = explode(',', $response['loc']);
            $location = ['latitude' => $lat, 'longitude' => $lng];
        }

        // Update or create the lookup
        IpLookup::updateOrCreate(
            ['ip' => $ip],
            [
                'hostname' => $response['hostname'] ?? null,
                'city' => $response['city'] ?? null,
                'region' => $response['region'] ?? null,
                'country' => $response['country'] ?? null,
                'latitude' => $location['latitude'] ?? null,
                'longitude' => $location['longitude'] ?? null,
                'org' => $response['org'] ?? null,
                'postal' => $response['postal'] ?? null,
                'timezone' => $response['timezone'] ?? null,
                'raw_data' => $response,
                'last_lookup_at' => now(),
            ]
        );

        return $response;
    }

    /**
     * Get geolocation information with database caching
     *
     * @return array{lat: string, lng: string}|null
     *
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getGeoLocation(?string $ip = null): ?array
    {
        $details = $this->getIpDetails($ip);

        if (! isset($details['loc'])) {
            return null;
        }

        [$lat, $lng] = explode(',', $details['loc']);

        return [
            'lat' => $lat,
            'lng' => $lng,
        ];
    }

    /**
     * Check if a lookup is still valid based on configured threshold
     */
    private function isLookupValid(IpLookup $lookup): bool
    {
        return $lookup->last_lookup_at->greaterThan(
            now()->subDays($this->refreshThresholdDays)
        );
    }
}
