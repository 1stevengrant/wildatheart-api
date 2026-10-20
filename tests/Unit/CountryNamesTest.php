<?php

use App\Support\CountryNames;

it('expands a country into its stored forms', function (string $input, array $expected) {
    expect(CountryNames::variants($input))->toContain(...$expected);
})->with([
    'name' => ['United States', ['US', 'United States', 'usa']],
    'lowercase code' => ['us', ['US', 'United States']],
    'alias' => ['UK', ['GB', 'United Kingdom', 'uk']],
    'other country' => ['Australia', ['AU', 'Australia']],
]);

it('passes unknown values through', function () {
    expect(CountryNames::variants('Narnia'))->toBe(['Narnia']);
});
