<?php

use Statamic\Facades\Blueprint;
use Statamic\Facades\Collection;
use App\DataTypeObjects\ContentPreview;

it('serialises the Statamic collection and blueprint to handle and title records', function () {
    $collection = Collection::make('events')->title('Events');
    $blueprint = Blueprint::make('multiday_retreat')->setContents(['title' => 'Event Landing Page']);

    $preview = new ContentPreview;
    $preview->id = 'entry-1';
    $preview->title = 'Retreat';
    $preview->collection = $collection;
    $preview->blueprint = $blueprint;

    $array = $preview->toArray();

    expect($array['collection'])->toBe(['title' => 'Events', 'handle' => 'events'])
        ->and($array['blueprint'])->toBe(['title' => 'Event Landing Page', 'handle' => 'multiday_retreat'])
        ->and($array['id'])->toBe('entry-1')
        ->and($array['related_content'])->toBeNull();
});
