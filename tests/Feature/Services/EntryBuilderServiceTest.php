<?php

use Statamic\Facades\Entry;
use App\Services\EntryBuilderService;

beforeEach(function () {
    $this->service = new EntryBuilderService;
});

it('builds a response from a simple entry', function () {
    $entry = Entry::make()
        ->collection('pages')
        ->slug('test-page');

    $entry->set('title', 'Test Page');
    $entry->save();

    $savedEntry = Entry::query()
        ->where('collection', 'pages')
        ->where('slug', 'test-page')
        ->first();

    $response = $this->service->buildResponseFromEntry($savedEntry);

    expect($response)
        ->toBeArray()
        ->toHaveKey('title', 'Test Page')
        ->toHaveKey('slug', 'test-page')
        ->not->toHaveKey('api_url')
        ->not->toHaveKey('edit_url')
        ->not->toHaveKey('is_entry');
});

it('removes unwanted properties from response', function () {
    $entry = Entry::make()
        ->collection('pages')
        ->slug('cleanup-test');

    $entry->set('title', 'Cleanup Test');
    $entry->save();

    $savedEntry = Entry::query()
        ->where('collection', 'pages')
        ->where('slug', 'cleanup-test')
        ->first();

    $response = $this->service->buildResponseFromEntry($savedEntry);

    $removedProperties = [
        'api_url',
        'date',
        'is_entry',
        'origin_id',
        'order',
        'mount',
        'edit_url',
        'updated_by',
        'updated_at',
        'permalink',
        'origin',
        'last_modified',
    ];

    foreach ($removedProperties as $property) {
        expect($response)->not->toHaveKey($property);
    }
});
