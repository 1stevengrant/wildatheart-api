<?php

use Illuminate\Support\Facades\Http;

it('returns the same response as production for the media page', function () {
    $productionResponse = Http::withoutVerifying()
        ->withBasicAuth('wah', 'wah')
        ->get('https://local.wildatheart.org/api/content/v1/page/media');

    $localResponse = Http::withoutVerifying()->get('https://wahfires.test/api/content/v1/page/media');

    expect($productionResponse->status())->toBe(200, 'Production response was not 200');
    expect($localResponse->status())->toBe(200, 'Local response was not 200');

    expect(normalizeJson($localResponse->json()))
        ->toBe(normalizeJson($productionResponse->json()));
})->group('comparison');

it('returns the same response as production for a custom media item', function () {
    $productionResponse = Http::withoutVerifying()
        ->withBasicAuth('wah', 'wah')
        ->get('https://local.wildatheart.org/api/content/v1/media-or-series/the-series/chapter-1-mark');

    $localResponse = Http::withoutVerifying()
        ->get('https://wahfires.test/api/content/v1/media-or-series/the-series/chapter-1-mark');

    expect($productionResponse->status())->toBe(200, 'Production response was not 200');
    expect($localResponse->status())->toBe(200, 'Local response was not 200');

    $normalizedLocal = normalizeJson($localResponse->json());
    $normalizedProd = normalizeJson($productionResponse->json());

    expect($normalizedLocal['blueprint']['handle'])->toBe('custom_media_item');

    // Verify blocks are not empty/null-filled (regression for union type docblock issue)
    foreach ($normalizedLocal['blocks'] as $i => $block) {
        $nonNullFields = collect($block)->reject(fn ($v) => is_null($v))->count();
        expect($nonNullFields)->toBeGreaterThan(1, "Block {$i} ({$block['type']}) has mostly null fields");
    }

    expect($normalizedLocal)->toBe($normalizedProd);
})->group('comparison');

/**
 * Recursively normalize environment-specific values (URLs, domains)
 * so responses from different environments can be compared.
 */
function normalizeJson(mixed $data): mixed
{
    if (is_string($data)) {
        // Replace known domain variations with a placeholder
        $domains = [
            'https://local.wildatheart.org',
            'https://local-staging.wildatheart.org',
            'https://wahfires.test',
            'https://proxy-dev.wildatheart.org',
            'https://wildatheart1.pages.dev',
            'https://www.wildatheart.org',
        ];

        return str_replace($domains, 'https://NORMALIZED', $data);
    }

    if (is_array($data)) {
        // Remove keys that are inherently environment-specific
        $ignoredKeys = ['edit_url', 'api_url', 'playtime'];

        // Normalize S3 container config differences (renamed in v6 branch)
        if (isset($data['title']) && $data['title'] === 'WordPress S3') {
            $data['title'] = 'S3';
        }

        if (isset($data['disk']) && $data['disk'] === 'wordpress') {
            $data['disk'] = 's3';
        }

        // Normalize Statamic v6 serialized link arrays back to URL strings
        if (array_key_exists('url', $data) && count($data) === 1) {
            return $data['url'];
        }

        if (isset($data['url'], $data['permalink']) && is_string($data['url']) && count($data) <= 6) {
            return $data['url'];
        }

        return collect($data)
            ->except($ignoredKeys)
            ->map(fn ($value) => normalizeJson($value))
            ->all();
    }

    return $data;
}
