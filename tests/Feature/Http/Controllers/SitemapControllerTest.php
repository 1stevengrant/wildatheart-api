<?php

use Statamic\Facades\Entry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

function makeSitemapEvent(string $slug, array $data = []): void
{
    Entry::make()
        ->collection('events')
        ->blueprint('multiday_retreat')
        ->slug($slug)
        ->published(true)
        ->date(now()->subDay())
        ->data(['title' => $slug, ...$data])
        ->save();
}

beforeEach(function () {
    Cache::forget('sitemap_pages.v3');
});

it('lists entries with their stored uri, last modified date and priority', function () {
    makeSitemapEvent('spring-retreat', ['sitemap_priority' => 0.8]);
    makeSitemapEvent('autumn-retreat');

    $response = $this->getJson(route('content.v1.seo.sitemap'));

    $response->assertOk()
        ->assertJsonCount(2)
        ->assertJsonFragment([
            'loc' => '/events/spring-retreat',
            'lastmod' => now()->format('Y-m-d'),
            'priority' => 0.8,
        ])
        ->assertJsonFragment([
            'loc' => '/events/autumn-retreat',
            'priority' => 0.5,
        ]);
});

it('leaves out entries hidden from search engines', function () {
    makeSitemapEvent('public-retreat', ['hide_from_search_engines' => false]);
    makeSitemapEvent('hidden-retreat', ['hide_from_search_engines' => true]);

    $response = $this->getJson(route('content.v1.seo.sitemap'));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['loc' => '/events/public-retreat']);
});

it('builds the sitemap with a single entries query for media items in a series', function () {
    $series = Entry::make()
        ->collection('media_series')
        ->blueprint('custom_series')
        ->slug('wild-at-heart')
        ->published(true)
        ->data(['title' => 'Wild at Heart']);

    $series->save();

    collect(range(1, 10))->each(fn (int $number) => Entry::make()
        ->collection('media_item')
        ->blueprint('custom_media_item')
        ->slug("session-{$number}")
        ->published(true)
        ->data([
            'title' => "Session {$number}",
            'series' => "entry::{$series->id()}",
        ])
        ->save());

    DB::enableQueryLog();

    $this->getJson(route('content.v1.seo.sitemap'))
        ->assertOk()
        ->assertJsonCount(11)
        ->assertJsonFragment(['loc' => '/media/wild-at-heart/session-1']);

    $entryQueries = collect(DB::getQueryLog())
        ->filter(fn (array $query): bool => str_contains($query['query'], '`entries`'));

    expect($entryQueries)->toHaveCount(1);
});
