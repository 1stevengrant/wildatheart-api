<?php

namespace App\Console\Commands;

use App\Jobs\AddRedirect;
use App\Services\RedirectService;
use Exception;
use Illuminate\Console\Command;

class BulkImportRedirects extends Command
{
    protected $signature = 'app:bulk-import-redirects {filePath : A text file containing redirects to import.}';

    protected $description = 'Imports redirects into Cloudflare KV';

    public function handle(RedirectService $redirectService)
    {
        $filePath = $this->argument('filePath');
        $filePathInDir = "redirects/$filePath";

        try {
            $redirects = $redirectService->getRedirectsFromFile($filePathInDir);

            foreach ($redirects as $redirect) {
                $this->info("Dispatching job for {$redirect['source']} -> {$redirect['destination']}");
                AddRedirect::dispatch($redirect['source'], $redirect['destination']);
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }
}
