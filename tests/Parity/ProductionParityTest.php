<?php

/**
 * Production Parity Tests
 *
 * Verify local API responses match production in status code and structure.
 * Run before merging upgrade branches to catch API contract changes.
 *
 * Uses the local dev database (not wahlocal_test) so real Statamic content
 * and database-stored globals are available.
 *
 * Usage: php artisan test --group=production-parity
 */

use Illuminate\Support\Facades\Http;

const PRODUCTION_API_BASE = 'https://local.wildatheart.org/api';

const PARITY_ENDPOINTS = [
    'v1/stats',
    'v1/navs/all',
    'v1/navs/desktop',
    'v1/navs/mobile',
    'v1/events',
    'v1/events/types',
    'content/media/redirects',
    'content/experiences/all',
    'content/v1/seo/sitemap',
    'content/v1/search',
    'content/v1/blogs',
    'content/v1/daily-reading',
    'content/v1/daily-reading/recent',
    'content/v1/prayers',
    'content/v1/events',
    'content/v1/alerts',
    'content/v1/subsplash/daily-readings',
    'content/v1/subsplash/daily-readings/list',
    'content/v1/subsplash/prayers',
    'events/all',
];

beforeAll(function () {
    $productionResponses = [];

    try {
        $responses = Http::pool(function ($pool) {
            foreach (PARITY_ENDPOINTS as $path) {
                $pool->as($path)
                    ->timeout(15)
                    ->accept('application/json')
                    ->get(PRODUCTION_API_BASE . '/' . $path);
            }
        });

        foreach (PARITY_ENDPOINTS as $path) {
            $response = $responses[$path];

            if ($response instanceof \Illuminate\Http\Client\Response) {
                $productionResponses[$path] = [
                    'status' => $response->status(),
                    'body' => $response->json(),
                    'reachable' => true,
                ];
            } else {
                $productionResponses[$path] = [
                    'status' => 0,
                    'body' => null,
                    'reachable' => false,
                    'error' => $response->getMessage(),
                ];
            }
        }
    } catch (\Exception $e) {
        foreach (PARITY_ENDPOINTS as $path) {
            $productionResponses[$path] = [
                'status' => 0,
                'body' => null,
                'reachable' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    Tests\ProductionParityTestCase::$productionResponses = $productionResponses;
});

function extractErrorFromResponse(\Illuminate\Testing\TestResponse $response): string
{
    $content = $response->getContent();
    $json = json_decode($content, true);

    if (is_array($json) && isset($json['message'])) {
        return $json['message'];
    }

    return mb_substr($content, 0, 200);
}

it('matches production response for {endpoint}', function (string $path) {
    $production = Tests\ProductionParityTestCase::$productionResponses[$path] ?? null;

    if (! $production || ! $production['reachable']) {
        $error = $production['error'] ?? 'No response cached';
        $this->markTestSkipped("Production unreachable: {$error}");
    }

    $local = $this->get("/api/$path", ['Accept' => 'application/json']);

    // 1. Status code parity
    $errorDetail = $local->getStatusCode() >= 500
        ? "\nLocal error: " . extractErrorFromResponse($local)
        : '';

    expect($local->getStatusCode())->toBe(
        $production['status'],
        "Status mismatch for /api/$path: production={$production['status']}, local={$local->getStatusCode()}$errorDetail"
    );

    if ($production['status'] !== 200 || $local->getStatusCode() !== 200) {
        return;
    }

    $prodBody = $production['body'];
    $localBody = json_decode($local->getContent(), true);

    // Skip structure comparison for non-JSON responses (e.g. plain text redirects)
    if ($prodBody === null || $localBody === null) {
        return;
    }

    // Skip if responses are not arrays (scalar JSON values)
    if (! is_array($prodBody) || ! is_array($localBody)) {
        expect(gettype($localBody))->toBe(
            gettype($prodBody),
            "Response type mismatch for /api/$path"
        );

        return;
    }

    $prodIsList = empty($prodBody) || array_is_list($prodBody);
    $localIsList = empty($localBody) || array_is_list($localBody);

    // 2. Response shape parity (list vs object)
    expect($localIsList)->toBe(
        $prodIsList,
        "Shape mismatch for /api/$path: production=" . ($prodIsList ? 'list' : 'object') .
        ', local=' . ($localIsList ? 'list' : 'object')
    );

    // 3. For object responses, compare top-level keys
    if (! $prodIsList && ! $localIsList) {
        $prodKeys = array_keys($prodBody);
        $localKeys = array_keys($localBody);
        sort($prodKeys);
        sort($localKeys);

        expect($localKeys)->toBe(
            $prodKeys,
            "Top-level keys mismatch for /api/$path\nProduction: " . implode(', ', $prodKeys) .
            "\nLocal: " . implode(', ', $localKeys)
        );
    }

    // 4. For populated list responses, compare first item keys
    if ($prodIsList && $localIsList && ! empty($prodBody) && ! empty($localBody)) {
        $prodFirst = $prodBody[0];
        $localFirst = $localBody[0];

        if (is_array($prodFirst) && ! array_is_list($prodFirst) &&
            is_array($localFirst) && ! array_is_list($localFirst)) {
            $prodItemKeys = array_keys($prodFirst);
            $localItemKeys = array_keys($localFirst);
            sort($prodItemKeys);
            sort($localItemKeys);

            expect($localItemKeys)->toBe(
                $prodItemKeys,
                "Item keys mismatch for /api/$path\nProduction: " . implode(', ', $prodItemKeys) .
                "\nLocal: " . implode(', ', $localItemKeys)
            );
        }
    }
})->with(
    collect(PARITY_ENDPOINTS)->mapWithKeys(fn ($path) => ["GET /api/$path" => [$path]])->all()
)->group('production-parity');
