<?php

namespace App\Services;

class GeographyService
{
    private readonly array $regions;

    public function __construct()
    {
        $this->regions = [
            'europe' => [
                'north' => 71.0,
                'south' => 36.0,
                'west' => -25.0,
                'east' => 40.0,
            ],
            'north_america' => [
                'north' => 83.0,
                'south' => 15.0,
                'west' => -168.0,
                'east' => -52.0,
            ],
            'south_america' => [
                'north' => 13.0,
                'south' => -56.0,
                'west' => -82.0,
                'east' => -34.0,
            ],
            'africa' => [
                'north' => 37.0,
                'south' => -35.0,
                'west' => -18.0,
                'east' => 52.0,
            ],
            'asia' => [
                'north' => 77.0,
                'south' => -10.0,
                'west' => 26.0,
                'east' => 180.0,
            ],
            'oceania' => [
                'north' => -10.0,
                'south' => -47.0,
                'west' => 112.0,
                'east' => 180.0,
            ],
        ];
    }

    public function getVisibleRegions(float $swLat, float $swLng, float $neLat, float $neLng): array
    {
        $visibleRegions = [];

        foreach ($this->regions as $region => $coords) {
            // Check if there's any overlap between viewport and region (latitude)
            $overlapsLat = ! ($neLat < $coords['south'] || $swLat > $coords['north']);

            // Handle longitude wrap-around for regions that cross the date line
            $overlapsLng = false;

            // Check if the viewport crosses the date line
            $viewportCrossesDateLine = $swLng > $neLng;

            if ($viewportCrossesDateLine) {
                // Viewport crosses the date line
                $overlapsLng = ($coords['east'] >= $swLng && $coords['east'] <= 180) ||
                             ($coords['west'] >= -180 && $coords['west'] <= $neLng) ||
                             ($coords['west'] <= $neLng && $coords['east'] >= $swLng);
            } elseif ($coords['west'] > $coords['east']) {
                // Region crosses the date line, viewport doesn't
                $overlapsLng = $neLng >= $coords['west'] || $swLng <= $coords['east'];
            } else {
                // Normal case: neither crosses the date line
                $overlapsLng = ! ($neLng < $coords['west'] || $swLng > $coords['east']);
            }

            if ($overlapsLat && $overlapsLng) {
                $visibleRegions[] = $region;
            }
        }

        return $visibleRegions;
    }
}
