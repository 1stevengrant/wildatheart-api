<?php

use App\Services\BlockBuilderService;
use App\DataTypeObjects\SlideBlock;
use App\Services\BunnyCDNService;
use App\Services\PreviewBuilderService;
use App\Services\SubsplashService;
use Statamic\Facades\Entry;

beforeEach(function () {
  $this->seed(\Database\Seeders\DatabaseSeeder::class);
  $this->subsplashService = Mockery::mock(SubsplashService::class);
  $this->bunnyCDNService = Mockery::mock(BunnyCDNService::class);

  // Create PreviewBuilderService with mocked dependencies
  $previewBuilderService = new PreviewBuilderService(
    $this->bunnyCDNService,
    $this->subsplashService
  );
  $this->service = new BlockBuilderService($previewBuilderService);
});

it('processes a hero title block correctly', function () {

  $mediaItem = Entry::make()
    ->collection("media_item")
    ->slug("podcast-1");

  $mediaItem->set("title", 'test item');
  $mediaItem->save();

  $block = [
    'type' => 'hero_title',
    'hero_title' => 'Welcome to our site',
    'text_style' => 'bold'
  ];

  $entry = Entry::make()->collection("pages")->slug("test");

  $testBlocks = [[
    "section_title" => "Video Series",
    "link_to_more" => null,
    "media_series" => null,
    "media_items" => null,
    "media_items" => null,
    "focused_item" => null,
    "slides" => [
      [
        "limit" => 10,
        "enabled" => true,
        "type" => "content_in_taxonomy",
        "collections_to_load_from" => [
          "media_item"
        ]
      ]
    ],
    "type" => "media_slider"
  ]];
  $entry->set('blocks', $testBlocks);


  $result = $this->service->build($entry->blocks);

  expect($result[0]['slides'])->toHaveCount(1)
    ->and($result[0]['slides'][0])->toBeInstanceOf(SlideBlock::class)
    ->and($result[0]['slides'][0]->title)->toBe('test item');
});
