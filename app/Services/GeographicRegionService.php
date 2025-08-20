<?php

namespace App\Services;

class GeographicRegionService
{
    public const string REGION_EUROPE = 'europe';

    public const string REGION_NORTH_AMERICA = 'north_america';

    public const string REGION_SOUTH_AMERICA = 'south_america';

    public const string REGION_ASIA = 'asia';

    public const string REGION_AFRICA = 'africa';

    public const string REGION_OCEANIA = 'oceania';

    /**
     * Country to region mappings
     * Using ISO 3166-1 alpha-2 country codes
     */
    private const array REGIONS = [
        self::REGION_EUROPE => [
            'GB', 'UK', 'DE', 'FR', 'IT', 'ES', 'PT', 'NL', 'BE', 'LU',
            'CH', 'AT', 'DK', 'SE', 'NO', 'FI', 'IS', 'IE', 'PL', 'CZ',
            'SK', 'HU', 'RO', 'BG', 'GR', 'HR', 'SI', 'RS', 'BA', 'ME',
            'MK', 'AL', 'EE', 'LV', 'LT', 'BY', 'UA', 'MD', 'MT', 'CY',
        ],
        self::REGION_NORTH_AMERICA => [
            'US', 'CA', 'MX', 'GT', 'BZ', 'SV', 'HN', 'NI', 'CR', 'PA',
            'CU', 'DO', 'HT', 'JM', 'TT', 'BB', 'BS', 'AG', 'DM', 'GD',
            'KN', 'LC', 'VC', 'PR', 'VI', 'AW', 'CW', 'SX', 'BM', 'GL',
        ],
        self::REGION_SOUTH_AMERICA => [
            'BR', 'AR', 'CL', 'PE', 'CO', 'VE', 'EC', 'BO', 'PY', 'UY',
            'GY', 'SR', 'GF', 'FK',
        ],
        self::REGION_ASIA => [
            'CN', 'IN', 'ID', 'PK', 'BD', 'JP', 'PH', 'VN', 'TH', 'MM',
            'KR', 'MY', 'UZ', 'SA', 'AF', 'IQ', 'IR', 'YE', 'SY', 'KH',
            'JO', 'AZ', 'AE', 'TJ', 'IL', 'HK', 'LA', 'LB', 'SG', 'OM',
            'KW', 'GE', 'MN', 'AM', 'QA', 'BH', 'TL', 'BN', 'MO', 'BT',
            'MV', 'TW', 'TM', 'KG', 'PS', 'NP', 'KZ', 'LK',
        ],
        self::REGION_AFRICA => [
            'NG', 'ET', 'EG', 'CD', 'ZA', 'TZ', 'KE', 'UG', 'DZ', 'SD',
            'MA', 'AO', 'GH', 'MZ', 'MG', 'CM', 'CI', 'NE', 'BF', 'ML',
            'MW', 'ZM', 'SN', 'SO', 'TD', 'ZW', 'GN', 'RW', 'BJ', 'TN',
            'BI', 'SS', 'TG', 'SL', 'LY', 'LA', 'LR', 'MR', 'ER', 'GM',
            'BW', 'GA', 'LS', 'GW', 'GQ', 'MU', 'SZ', 'DJ', 'RE', 'KM',
            'CV', 'YT', 'ST', 'SC', 'NA',
        ],
        self::REGION_OCEANIA => [
            'AU', 'PG', 'NZ', 'FJ', 'SB', 'NC', 'PF', 'VU', 'WS', 'KI',
            'TO', 'FM', 'PW', 'CK', 'NU', 'TV', 'NR', 'MP', 'GU', 'AS',
            'MH', 'PN', 'TK', 'WF',
        ],
    ];

    /**
     * Map of full country names to ISO codes
     */
    private const array COUNTRY_NAME_MAP = [
        'UNITED STATES' => 'US',
        'UNITED KINGDOM' => 'GB',
        'NEW ZEALAND' => 'NZ',
        'AUSTRALIA' => 'AU',
        'CANADA' => 'CA',
        'GERMANY' => 'DE',
        'FRANCE' => 'FR',
        'SPAIN' => 'ES',
        'ITALY' => 'IT',
        'NETHERLANDS' => 'NL',
        'BELGIUM' => 'BE',
        'SWITZERLAND' => 'CH',
        'AUSTRIA' => 'AT',
        'POLAND' => 'PL',
        'SWEDEN' => 'SE',
        'NORWAY' => 'NO',
        'DENMARK' => 'DK',
        'FINLAND' => 'FI',
        'IRELAND' => 'IE',
        'SOUTH AFRICA' => 'ZA',
        'BRAZIL' => 'BR',
        'MEXICO' => 'MX',
        'ARGENTINA' => 'AR',
        'JAPAN' => 'JP',
        'CHINA' => 'CN',
        'INDIA' => 'IN',
        'SINGAPORE' => 'SG',
    ];

    /**
     * Get the region for a country code or country name
     */
    public function getRegionForCountry(string $country): ?string
    {
        $country = mb_strtoupper(trim($country));

        // First check if it's a full country name and convert to ISO code
        if (isset(self::COUNTRY_NAME_MAP[$country])) {
            $country = self::COUNTRY_NAME_MAP[$country];
        }

        // Handle special cases and variations
        if ($country === 'UK') {
            $country = 'GB';
        }

        foreach (self::REGIONS as $region => $countries) {
            if (in_array($country, $countries, true)) {
                return $region;
            }
        }

        return null;
    }

    /**
     * Get all country codes for a region
     */
    public function getCountriesInRegion(string $region): array
    {
        return self::REGIONS[$region] ?? [];
    }

    /**
     * Get region from viewport coordinates
     * This uses a simple approach based on longitude/latitude ranges
     */
    public function getRegionFromViewport(
        float $swLatitude,
        float $swLongitude,
        float $neLatitude,
        float $neLongitude
    ): ?string {
        // Calculate center point of viewport
        $centerLat = ($swLatitude + $neLatitude) / 2;
        $centerLng = ($swLongitude + $neLongitude) / 2;

        // Normalize longitude to -180 to 180 range
        while ($centerLng > 180) {
            $centerLng -= 360;
        }
        while ($centerLng < -180) {
            $centerLng += 360;
        }

        // Rough geographic boundaries for regions
        // Check Oceania first since it can overlap with Asia longitude
        // Oceania: roughly -50° to 10° N, 110° to -150° E (includes Pacific islands)
        // Australia is around -10 to -45 lat, 110 to 155 lng
        // New Zealand is around -35 to -47 lat, 165 to 180 lng
        if ($centerLat >= -50 && $centerLat <= 10) {
            if (($centerLng >= 110 && $centerLng <= 180) || ($centerLng >= -180 && $centerLng <= -150)) {
                return self::REGION_OCEANIA;
            }
        }

        // Europe: roughly 35° to 70° N, -15° to 40° E (extended west to include Ireland)
        if ($centerLat >= 35 && $centerLat <= 70 && $centerLng >= -15 && $centerLng <= 40) {
            return self::REGION_EUROPE;
        }

        // North America: roughly 15° to 75° N, -170° to -50° W
        if ($centerLat >= 15 && $centerLat <= 75 && $centerLng >= -170 && $centerLng <= -50) {
            return self::REGION_NORTH_AMERICA;
        }

        // South America: roughly -55° to 15° N, -85° to -35° W
        if ($centerLat >= -55 && $centerLat <= 15 && $centerLng >= -85 && $centerLng <= -35) {
            return self::REGION_SOUTH_AMERICA;
        }

        // Asia: roughly -10° to 75° N, 40° to 180° E
        if ($centerLat >= -10 && $centerLat <= 75 && $centerLng >= 40 && $centerLng <= 180) {
            return self::REGION_ASIA;
        }

        // Africa: roughly -35° to 35° N, -20° to 55° E
        if ($centerLat >= -35 && $centerLat <= 35 && $centerLng >= -20 && $centerLng <= 55) {
            return self::REGION_AFRICA;
        }

        // If we can't determine the region, return null
        return null;
    }
}
