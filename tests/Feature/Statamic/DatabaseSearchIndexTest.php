<?php

use Statamic\Search\Documents;
use App\Support\Search\DatabaseIndex;

function databaseIndex(string $name = 'test'): DatabaseIndex
{
    return new DatabaseIndex($name, ['fields' => ['title', 'summary']]);
}

it('does not exist until it has been built', function (): void {
    expect(databaseIndex()->exists())->toBeFalse();
});

it('exists after a rebuild even when nothing was indexed', function (): void {
    $index = databaseIndex();

    $index->update();

    expect($index->exists())->toBeTrue()
        ->and($index->lookup('anything'))->toBeEmpty();
});

it('finds documents matching every search term', function (): void {
    $index = databaseIndex();

    $index->insertDocuments(new Documents([
        'entry::1' => ['title' => 'Montana Fire', 'summary' => 'Weekly gathering'],
        'entry::2' => ['title' => 'Texas Fire', 'summary' => 'Monthly gathering'],
    ]));

    expect($index->lookup('fire')->pluck('reference')->all())->toEqualCanonicalizing(['entry::1', 'entry::2'])
        ->and($index->lookup('montana FIRE')->pluck('reference')->all())->toBe(['entry::1'])
        ->and($index->lookup('montana monthly'))->toBeEmpty();
});

it('ranks title matches above matches in later fields', function (): void {
    $index = databaseIndex();

    $index->insertDocuments(new Documents([
        'entry::1' => ['title' => 'Retreat', 'summary' => 'A captivating weekend'],
        'entry::2' => ['title' => 'Captivating', 'summary' => 'A retreat'],
    ]));

    expect($index->lookup('captivating')->pluck('reference')->all())->toBe(['entry::2', 'entry::1']);
});

it('replaces a document when it is indexed again', function (): void {
    $index = databaseIndex();

    $index->insertDocuments(new Documents(['entry::1' => ['title' => 'Old title']]));
    $index->insertDocuments(new Documents(['entry::1' => ['title' => 'New title']]));

    expect($index->lookup('title')->pluck('title')->all())->toBe(['New title']);
});

it('removes deleted documents', function (): void {
    $index = databaseIndex();

    $index->insertDocuments(new Documents(['entry::1' => ['title' => 'Montana Fire']]));

    $index->delete(new class
    {
        public function getSearchReference(): string
        {
            return 'entry::1';
        }
    });

    expect($index->lookup('montana'))->toBeEmpty();
});

it('keeps documents of other indexes when rebuilding', function (): void {
    databaseIndex('other')->insertDocuments(new Documents(['entry::1' => ['title' => 'Montana Fire']]));

    databaseIndex()->update();

    expect(databaseIndex('other')->lookup('montana'))->toHaveCount(1);
});

it('treats like wildcards in the query as literal characters', function (): void {
    $index = databaseIndex();

    $index->insertDocuments(new Documents(['entry::1' => ['title' => 'Montana Fire']]));

    expect($index->lookup('%'))->toBeEmpty()
        ->and($index->lookup('_'))->toBeEmpty();
});

it('returns nothing for a blank query', function (): void {
    expect(databaseIndex()->lookup('  '))->toBeEmpty();
});

it('returns results through the statamic search query builder', function (): void {
    $index = databaseIndex();

    $index->insertDocuments(new Documents(['entry::1' => ['title' => 'Montana Fire']]));

    $results = $index->search('montana')->withoutData()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->getRawResult()['reference'])->toBe('entry::1');
});
