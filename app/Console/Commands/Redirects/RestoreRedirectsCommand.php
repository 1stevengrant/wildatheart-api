<?php

namespace App\Console\Commands\Redirects;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Admin\AddRedirectsInBulkJob;

class RestoreRedirectsCommand extends Command
{
  protected $signature = 'app:restore-redirects {filePath : A text file containing redirects to import.}';

  protected $description = 'Restores redirects from a JSON file';

  public function handle()
  {
    $filePath = storage_path($this->argument('filePath'));

    if (! Storage::exists($filePath)) {
      $this->error("No redirects found in $filePath");
      return;
    }

    try {
      AddRedirectsInBulkJob::dispatch($filePath);
      $this->info('Created AddRedirectsInBulkJob');
    } catch (Exception $exception) {
      $this->error($exception->getMessage());
    }
  }
}
