<?php

use Illuminate\Support\Facades\Http;

function fetchProductionResponse(string $url): array
{
    try {
        $response = Http::timeout(15)
            ->withoutVerifying()
            ->withBasicAuth('wah', 'wah')
            ->accept('application/json')
            ->get($url);

        return [
            'status' => $response->status(),
            'body' => $response->json(),
            'reachable' => true,
        ];
    } catch (Exception $e) {
        return [
            'status' => 0,
            'body' => null,
            'reachable' => false,
            'error' => $e->getMessage(),
        ];
    }
}

it('returns the same response as production for the media page', function () {
    $production = fetchProductionResponse('https://local.wildatheart.org/api/content/v1/page/media');

    if (! $production['reachable']) {
        $this->markTestSkipped('Production unreachable: ' . ($production['error'] ?? 'Unknown error'));
    }

    $localResponse = $this->get('/api/content/v1/page/media', ['Accept' => 'application/json']);

    expect($production['status'])->toBe(200, 'Production response was not 200')
        ->and($localResponse->status())->toBe(200, 'Local response was not 200')
        ->and(normalizeJson($localResponse->json()))
        ->toBe(normalizeJson($production['body']));
})->group('comparison', 'production-parity');

it('returns the same response as production for a custom media item', function () {
    $production = fetchProductionResponse('https://local.wildatheart.org/api/content/v1/media-or-series/the-series/chapter-1-mark');

    if (! $production['reachable']) {
        $this->markTestSkipped('Production unreachable: ' . ($production['error'] ?? 'Unknown error'));
    }

    $localResponse = $this->get(
        '/api/content/v1/media-or-series/the-series/chapter-1-mark',
        ['Accept' => 'application/json'],
    );

    expect($production['status'])->toBe(200, 'Production response was not 200')
        ->and($localResponse->status())->toBe(200, 'Local response was not 200');

    $normalizedLocal = normalizeJson($localResponse->json());
    $normalizedProd = normalizeJson($production['body']);

    expect($normalizedLocal['blueprint']['handle'])->toBe('custom_media_item');

    foreach ($normalizedLocal['blocks'] as $i => $block) {
        $nonNullFields = collect($block)->reject(fn ($v) => is_null($v))->count();
        expect($nonNullFields)->toBeGreaterThan(1, "Block {$i} ({$block['type']}) has mostly null fields");
    }

    expect($normalizedLocal)->toBe($normalizedProd);
})->group('comparison', 'production-parity');

/**
 * Recursively normalize environment-specific values (URLs, domains)
 * so responses from different environments can be compared.
 */
function normalizeJson(mixed $data): mixed
{
    if (is_string($data)) {
        $domains = [
            'https://local.wildatheart.org',
            'https://local-staging.wildatheart.org',
            'https://wahfires.test',
            'https://proxy-dev.wildatheart.org',
            'https://wildatheart1.pages.dev',
            'https://www.wildatheart.org',
            'https://b-cdn.wildatheart.org',
        ];

        return str_replace($domains, '', $data);
    }

    if (is_array($data)) {
        $ignoredKeys = ['edit_url', 'api_url', 'playtime'];

        if (isset($data['title']) && $data['title'] === 'WordPress S3') {
            $data['title'] = 'S3';
        }

        if (isset($data['disk']) && $data['disk'] === 'wordpress') {
            $data['disk'] = 's3';
        }

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
