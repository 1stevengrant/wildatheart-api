<?php

namespace App\Console\Commands;

use App\Services\SubsplashService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Statamic\Entries\Collection;
use Statamic\Facades\Entry;

class GetLatestSubsplashMedia extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'app:get-latest-subsplash-media';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Imports latest Subplash items.';

  public function handle(): void
  {
    $subsplash = new SubsplashService;
    $subsplashMediaItems = $subsplash->subsplashFetch("https://core.subsplash.com/media/v1/media-items?filter%5Bstatus%5D=published&filter%5Bapp_key%5D=5289PV");

    $subsplashMediaItems = collect($subsplashMediaItems["_embedded"]["media-items"]);
    foreach ($subsplashMediaItems as $item) {
      $mediaItem = Entry::query()
        // ->where('id', $item['id'])
        ->whereJsonContains('data->subsplash_media_item_id', $item['id'])
        ->first();

      if (!$mediaItem) {
        $this->info($item['title']);
        $this->info($item['id']);
        $this->info('Creating media item');
        $subsplash->saveMediaItemAsEntry($item);
      } else {
      }
    }
  }
}
