<?php

namespace App\Support;

use Locale;

/**
 * Country values are stored inconsistently across the platform: ISO codes in
 * any case ("us", "GB") and English names ("United States"). This expands a
 * country into every stored form so exact matching catches all of them.
 */
class CountryNames
{
    /** @var array<string, string> */
    private const array ALIASES = [
        'usa' => 'US',
        'america' => 'US',
        'united states of america' => 'US',
        'uk' => 'GB',
        'great britain' => 'GB',
        'britain' => 'GB',
        'england' => 'GB',
    ];

    /** @var array<string, string>|null */
    private static ?array $namesByCode = null;

    /**
     * @return array<int, string>
     */
    public static function variants(string $country): array
    {
        $country = mb_trim($country);

        $code = self::codeFor($country);

        if ($code === null) {
            return [$country];
        }

        $variants = [$code, self::namesByCode()[$code]];

        foreach (self::ALIASES as $alias => $aliasCode) {
            if ($aliasCode === $code) {
                $variants[] = $alias;
            }
        }

        return array_values(array_unique([...$variants, $country]));
    }

    private static function codeFor(string $country): ?string
    {
        $lowercase = mb_strtolower($country);

        if (isset(self::ALIASES[$lowercase])) {
            return self::ALIASES[$lowercase];
        }

        if (isset(self::namesByCode()[mb_strtoupper($country)])) {
            return mb_strtoupper($country);
        }

        foreach (self::namesByCode() as $code => $name) {
            if (mb_strtolower($name) === $lowercase) {
                return $code;
            }
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private static function namesByCode(): array
    {
        if (self::$namesByCode !== null) {
            return self::$namesByCode;
        }

        if (! class_exists(Locale::class)) {
            return self::$namesByCode = [];
        }

        $names = [];

        foreach (range('A', 'Z') as $first) {
            foreach (range('A', 'Z') as $second) {
                $code = $first . $second;
                $name = Locale::getDisplayRegion("-{$code}", 'en');

                if ($name !== '' && $name !== $code) {
                    $names[$code] = $name;
                }
            }
        }

        return self::$namesByCode = $names;
    }
}
