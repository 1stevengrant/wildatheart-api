<?php

use App\Models\Event;
use App\Models\State;
use App\Models\EventType;

if (! function_exists('getStateAbbreviation')) {
    function getStateAbbreviation(?string $stateName = null): ?string
    {
        if ($stateName === null) {
            return null;
        }

        if (mb_strlen($stateName) === 2) {
            return mb_strtoupper($stateName);
        }

        $normalizedName = ucwords(mb_strtolower(trim($stateName)));

        static $stateAbbreviations = null;

        if ($stateAbbreviations === null) {
            $stateAbbreviations = cache()->rememberForever('state_abbreviations', function () {
                return State::all()->pluck('code', 'name')->toArray();
            });
        }

        return $stateAbbreviations[$normalizedName] ?? null;
    }
}

if (! function_exists('getStateName')) {
    function getStateName(?string $stateCode = null): string
    {
        return cache()->rememberForever('state_names', function () {
            return State::all()->pluck('name', 'code')->toArray();
        })[$stateCode] ?? '';
    }
}

if (! function_exists('getCountries')) {
    function getCountries(): array
    {
        return [
            'US' => 'United States',
            'CA' => 'Canada',
            'UK' => 'United Kingdom',
            'AU' => 'Australia',
            'NZ' => 'New Zealand',
            'AF' => 'Afghanistan',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua and Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia and Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'BQ' => 'British Antarctic Territory',
            'IO' => 'British Indian Ocean Territory',
            'VG' => 'British Virgin Islands',
            'BN' => 'Brunei',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CT' => 'Canton and Enderbury Islands',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos [Keeling] Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo - Brazzaville',
            'CD' => 'Congo - Kinshasa',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'CI' => 'Côte d’Ivoire',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'NQ' => 'Dronning Maud Land',
            'DD' => 'East Germany',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'FQ' => 'French Southern and Antarctic Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island and McDonald Islands',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong SAR China',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JT' => 'Johnston Island',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Laos',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macau SAR China',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'FX' => 'Metropolitan France',
            'MX' => 'Mexico',
            'FM' => 'Micronesia',
            'MI' => 'Midway Islands',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar [Burma]',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NT' => 'Neutral Zone',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'KP' => 'North Korea',
            'VD' => 'North Vietnam',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PC' => 'Pacific Islands Trust Territory',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territories',
            'PA' => 'Panama',
            'PZ' => 'Panama Canal Zone',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'YD' => "People's Democratic Republic of Yemen",
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn Islands',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'RE' => 'Réunion',
            'BL' => 'Saint Barthélemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts and Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre and Miquelon',
            'VC' => 'Saint Vincent and the Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'CS' => 'Serbia and Montenegro',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia and the South Sandwich Islands',
            'KR' => 'South Korea',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard and Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syria',
            'ST' => 'São Tomé and Príncipe',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad and Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks and Caicos Islands',
            'TV' => 'Tuvalu',
            'UM' => 'U.S. Minor Outlying Islands',
            'PU' => 'U.S. Miscellaneous Pacific Islands',
            'VI' => 'U.S. Virgin Islands',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'SU' => 'Union of Soviet Socialist Republics',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'ZZ' => 'Unknown or Invalid Region',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VA' => 'Vatican City',
            'VE' => 'Venezuela',
            'VN' => 'Vietnam',
            'WK' => 'Wake Island',
            'WF' => 'Wallis and Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
            'AX' => 'Åland Islands',
        ];
    }
}

if (! function_exists('getCountryCodeFromCountryName')) {
    function getCountryCodeFromCountryName(string $countryName): string
    {
        $normalizedName = mb_strtolower(trim($countryName));
        $countryCode = Cache::get("country_code:{$normalizedName}");

        if ($countryCode === null) {
            $country = \Squire\Models\Country::where('name', 'like', $countryName)->first();

            if ($country) {
                $countryCode = $country->code_2;
                Cache::put("country_code:{$normalizedName}", $countryCode, now()->addWeek());
            } else {
                Cache::put("country_code:{$normalizedName}", 'not_found', now()->addDay());

                return '';
            }
        }

        return $countryCode === 'not_found' ? '' : $countryCode;
    }
}

if (! function_exists('determineSexOfEvent')) {
    function determineSexOfEvent(EventType $eventType): string
    {
        return in_array($eventType->name, [EventType::CAPTIVATING_EXPERIENCE, EventType::CAPTIVATING_CORE_RETREAT, EventType::CAPTIVATING_FIRE]) ? Event::FOR_WOMEN : Event::FOR_MEN;
    }
}

if (! function_exists('deepArrayCompare')) {
    function deepArrayCompare($array1, $array2): bool
    {
        if (is_array($array1) && is_array($array2)) {
            if (count($array1) !== count($array2)) {
                return false;
            }

            foreach ($array1 as $key => $value) {
                if (! array_key_exists($key, $array2) || ! deepArrayCompare($value, $array2[$key])) {
                    return false;
                }
            }

            return true;
        }

        return $array1 === $array2;
    }
}
