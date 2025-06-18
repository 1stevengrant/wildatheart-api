<?php

namespace App\Console\Commands\Redirects;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Jobs\Admin\AddRedirectsInBulkJob;

class BulkImportRedirectsCommand extends Command
{
    protected $signature = 'app:bulk-import-redirects {filePath : A text file containing redirects to import.}';

    protected $description = 'Imports redirects into Cloudflare KV';

    public function handle()
    {
        $filePath = $this->argument('filePath');
        $filePathInDir = "app/redirects/$filePath";

        if (! Storage::exists($filePathInDir)) {
            $this->error("File not found: $filePathInDir");

            return;
        }

        try {
            AddRedirectsInBulkJob::dispatch($filePathInDir);
            $this->info('Created AddRedirectsInBulkJob');
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
