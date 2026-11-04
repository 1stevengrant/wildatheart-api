<?php

use Statamic\Facades\Entry;
use Statamic\Facades\Collection;

beforeEach(function () {
    Collection::findByHandle('media_item') ?? Collection::make('media_item')->routes('/media/{slug}')->save();

    $this->makeMediaItem = function (string $slug, int $position, string $series = 'series-1'): void {
        $entry = Entry::make()
            ->collection('media_item')
            ->blueprint('custom_media_item')
            ->id("id-{$slug}")
            ->slug($slug)
            ->date(now()->subDay());

        $entry->set('title', "Title {$slug}");
        $entry->set('description', "Description {$slug}");
        $entry->set('position', $position);
        $entry->set('series', "entry::{$series}");
        $entry->save();
    };
});

it('returns only public fields for the items in a series', function () {
    ($this->makeMediaItem)('first', 1);

    $response = $this->getJson(route('content.media.bySeriesId', ['slug' => 'series-1']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', 'id-first')
        ->assertJsonPath('0.title', 'Title first')
        ->assertJsonPath('0.description', 'Description first')
        ->assertJsonPath('0.position', 1)
        ->assertJsonPath('0.slug', 'first')
        ->assertJsonMissingPath('0.edit_url')
        ->assertJsonMissingPath('0.api_url')
        ->assertJsonMissingPath('0.updated_by')
        ->assertJsonMissingPath('0.private');

    expect(array_keys($response->json('0')))->each->toBeIn([
        'id',
        'title',
        'slug',
        'uri',
        'description',
        'media_type',
        'position',
        'is_coming_soon',
        'images',
    ]);
});

it('orders items by position and leaves out other series', function () {
    ($this->makeMediaItem)('second', 2);
    ($this->makeMediaItem)('first', 1);
    ($this->makeMediaItem)('elsewhere', 1, 'series-2');

    $response = $this->getJson(route('content.media.bySeriesId', ['slug' => 'series-1']));

    expect(array_column($response->json(), 'slug'))->toBe(['first', 'second']);
});

it('no longer serves the unversioned media item route', function () {
    ($this->makeMediaItem)('first', 1);

    $this->getJson('/api/content/media/first')->assertNotFound();
});
