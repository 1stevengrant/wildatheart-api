<?php

use Statamic\Facades\Blink;
use Statamic\Facades\Entry;
use Illuminate\Queue\Jobs\FakeJob;
use Illuminate\Support\Facades\DB;
use App\Support\MediaItemRouteLookups;
use Illuminate\Queue\Events\JobProcessing;

function makeRouteEntry(string $collection, string $blueprint, string $slug, array $data = []): Statamic\Entries\Entry
{
    $entry = Entry::make()
        ->collection($collection)
        ->blueprint($blueprint)
        ->slug($slug)
        ->published(true)
        ->data(['title' => $slug, ...$data]);

    $entry->save();

    return $entry;
}

function makeSeriesItem(string $slug, string $seriesId): Statamic\Entries\Entry
{
    return makeRouteEntry('media_item', 'custom_media_item', $slug, ['series' => "entry::{$seriesId}"]);
}

function freshUri(Statamic\Entries\Entry $entry): ?string
{
    Blink::store('entry-uris')->flush();

    return Entry::find($entry->id())->uri();
}

beforeEach(function () {
    MediaItemRouteLookups::flush();
});

it('routes media items in a podcast series under the podcast', function () {
    $series = makeRouteEntry('media_series', 'custom_series', 'wild-at-heart-podcast');
    makeRouteEntry('podcasts', 'podcast', 'the-podcast', ['subsplash_series_id' => $series->id()]);

    $episode = makeSeriesItem('episode-one', $series->id());

    expect(freshUri($episode))->toBe('/podcasts/the-podcast/episode-one');
});

it('routes media items in a series without a podcast under the series', function () {
    $series = makeRouteEntry('media_series', 'custom_series', 'becoming-a-king-series');

    $item = makeSeriesItem('session-video', $series->id());

    expect(freshUri($item))->toBe('/media/becoming-a-king-series/session-video');
});

it('routes experience sessions by their position in the experience', function () {
    $first = makeRouteEntry('media_item', 'experience_session', 'opening');
    $second = makeRouteEntry('media_item', 'experience_session', 'closing');

    $experience = makeRouteEntry('experiences', 'experience', 'captivating', [
        'sessions' => [$first->id(), $second->id()],
    ]);

    expect(freshUri($second))->toBe("{$experience->uri()}/session-2")
        ->and(freshUri($first))->toBe("{$experience->uri()}/session-1");
});

it('looks up podcasts once however many series items resolve their uri', function () {
    $series = makeRouteEntry('media_series', 'custom_series', 'long-series');
    makeRouteEntry('podcasts', 'podcast', 'long-podcast', ['subsplash_series_id' => $series->id()]);

    $items = collect(range(1, 5))->map(fn (int $number) => makeSeriesItem("episode-{$number}", $series->id()));

    Blink::store('entry-uris')->flush();
    MediaItemRouteLookups::flush();
    DB::enableQueryLog();

    $items->each(fn ($item) => Entry::find($item->id())->uri());

    $podcastQueries = collect(DB::getQueryLog())
        ->filter(fn (array $query): bool => in_array('podcasts', $query['bindings'], true));

    expect($podcastQueries)->toHaveCount(1);
});

it('picks up a podcast saved after the lookup was cached', function () {
    $series = makeRouteEntry('media_series', 'custom_series', 'new-podcast-series');
    $episode = makeSeriesItem('first-episode', $series->id());

    expect(freshUri($episode))->toBe('/media/new-podcast-series/first-episode');

    makeRouteEntry('podcasts', 'podcast', 'new-podcast', ['subsplash_series_id' => $series->id()]);

    expect(freshUri($episode))->toBe('/podcasts/new-podcast/first-episode');
});

it('clears the cached lookups before each queued job', function () {
    $series = makeRouteEntry('media_series', 'custom_series', 'worker-series');
    $episode = makeSeriesItem('worker-episode', $series->id());

    expect(freshUri($episode))->toBe('/media/worker-series/worker-episode');

    Entry::make()
        ->collection('podcasts')
        ->blueprint('podcast')
        ->slug('worker-podcast')
        ->published(true)
        ->data(['title' => 'worker-podcast', 'subsplash_series_id' => $series->id()])
        ->saveQuietly();

    expect(freshUri($episode))->toBe('/media/worker-series/worker-episode');

    event(new JobProcessing('redis', new FakeJob));

    expect(freshUri($episode))->toBe('/podcasts/worker-podcast/worker-episode');
});
