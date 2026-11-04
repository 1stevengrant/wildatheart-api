<?php

use Statamic\Facades\Entry;
use Statamic\Facades\Collection;

beforeEach(function () {
    Collection::findByHandle('media_item') ?? Collection::make('media_item')->routes('/media/{slug}')->save();
    Collection::findByHandle('media_series') ?? Collection::make('media_series')->routes('/series/{slug}')->save();

    $series = Entry::make()
        ->collection('media_series')
        ->blueprint('custom_series')
        ->id('series-1')
        ->slug('a-series');
    $series->set('title', 'A Series');
    $series->save();

    $item = Entry::make()
        ->collection('media_item')
        ->blueprint('custom_media_item')
        ->id('item-1')
        ->slug('an-item')
        ->date(now()->subDay());
    $item->set('title', 'An Item');
    $item->set('series', 'entry::series-1');
    $item->set('updated_by', 'editor-1');
    $item->save();
});

function internalFieldPaths(array $data, string $prefix = ''): array
{
    $paths = [];

    foreach ($data as $key => $value) {
        $path = "{$prefix}{$key}";

        if (in_array($key, ['edit_url', 'api_url', 'updated_by'], true)) {
            $paths[] = $path;
        }

        if (is_array($value)) {
            $paths = [...$paths, ...internalFieldPaths($value, "{$path}.")];
        }
    }

    return $paths;
}

it('leaves internal fields out of a single entry', function () {
    $response = $this->getJson('/api/content/collections/media_item/entries/item-1');

    $response->assertOk()
        ->assertJsonPath('data.title', 'An Item')
        ->assertJsonPath('data.slug', 'an-item');

    expect(internalFieldPaths($response->json()))->toBe([]);
});

it('leaves internal fields out of an entry listing', function () {
    $response = $this->getJson('/api/content/collections/media_item/entries');

    $response->assertOk()
        ->assertJsonPath('data.0.title', 'An Item');

    expect(internalFieldPaths($response->json('data')))->toBe([]);
});

it('keeps the modified dates the frontend reads', function () {
    $response = $this->getJson('/api/content/collections/media_item/entries/item-1');

    expect($response->json('data'))->toHaveKeys(['updated_at', 'last_modified', 'permalink']);
});
