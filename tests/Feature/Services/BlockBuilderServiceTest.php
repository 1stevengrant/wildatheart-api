<?php

namespace Tests\Feature\Services;

use App\DataTypeObjects\MediaItemsSlide;
use App\DataTypeObjects\StatamicValue;
use App\Services\BlockBuilderService;
use Illuminate\Support\Facades\Cache;
use Statamic\Entries\Entry;

beforeEach(function () {
  Cache::flush();
  $this->transistorService = new BlockBuilderService();
});

it('returns cached data when available', function () {

  $entry = Entry::make()
    ->published(true)
    ->blueprint('subsplash_media_item')
    ->collection('media_item')
    ->id('b3cf6993-7341-4204-9fd0-56e43913702f')
    ->slug("test-slug")
    ->data([
      'title' => 'Test Title',
      'subsplash_media_item_id' => 'b3cf6993-7341-4204-9fd0-56e43913702f',
    ]);

  $entry->save();


  $slide = [
    'limit' => StatamicValue::from(),
    'id' => 'testID',
    'type' => 'recently_added',
  ];

  $result = $this->transistorService->buildRecentlyAdded($slide);

  expect($result)->toBeInstanceOf(MediaItemsSlide::class);
  expect($result->slides)->toHaveCount(1);
});
