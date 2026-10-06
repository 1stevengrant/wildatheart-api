<?php

use Statamic\Facades\Entry;
use Statamic\Facades\Collection;

beforeEach(function () {
    Collection::findByHandle('media_item') ?? Collection::make('media_item')->save();
});

it('does not list the custom media item text media_type field as a column', function () {
    $columns = Collection::findByHandle('media_item')->entryBlueprint()->columns()->rejectUnlisted();

    expect($columns->has('media_type'))->toBeFalse();
});

it('can index-process every media item entry with the default blueprint columns', function () {
    $collection = Collection::findByHandle('media_item');

    $entry = Entry::make()
        ->collection('media_item')
        ->blueprint('subsplash_media_item')
        ->slug('array-media-type')
        ->data(['title' => 'Episode', 'media_type' => ['video']]);
    $entry->save();

    // Mirrors Statamic\Http\Resources\CP\Entries\ListedEntry::values(), which the relationship
    // picker runs against the collection's default blueprint for entries of every blueprint.
    $blueprint = $collection->entryBlueprint();

    $values = $blueprint->columns()->rejectUnlisted()->mapWithKeys(function ($column) use ($blueprint, $entry) {
        $field = $blueprint->field($column->field());

        return [$column->field() => $field?->setValue($entry->value($column->field()))->setParent($entry)->preProcessIndex()->value()];
    });

    expect($values->get('title'))->toBe('Episode');
});
