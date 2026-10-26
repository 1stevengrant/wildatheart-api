<?php

use App\Models\Event;
use App\Models\Activity;
use Statamic\Facades\Search;
use Statamic\Search\Documents;
use Illuminate\Support\Facades\DB;
use App\Support\Search\DatabaseIndex;
use StatamicRadPack\Runway\Search\Searchable;

it('indexes runway events in the cp index only', function (): void {
    $searchable = new Searchable(Event::factory()->create());

    expect(Search::index('cp')->shouldIndex($searchable))->toBeTrue()
        ->and(Search::index('default')->shouldIndex($searchable))->toBeFalse();
});

it('keeps the activity log out of the search indexes', function (): void {
    $searchable = new Searchable(Activity::create(['description' => 'Monthly email sent']));

    expect(Search::indexes()->filter->shouldIndex($searchable))->toBeEmpty();
});

it('uses the database driver when configured', function (): void {
    config()->set('statamic.search.indexes.cp.driver', 'database');

    expect(Search::index('cp'))->toBeInstanceOf(DatabaseIndex::class);
});

it('adds a saved event to an existing database index without rebuilding it', function (): void {
    config()->set('statamic.search.indexes.cp.driver', 'database');

    $index = Search::index('cp');
    $index->update();
    $index->insertDocuments(new Documents(['entry::untouched' => ['title' => 'Untouched']]));

    $event = Event::factory()->create();

    expect(DB::table('search_documents')->where('index', $index->name())->pluck('reference'))
        ->toContain('entry::untouched')
        ->toContain((new Searchable($event))->getSearchReference());
});
