<?php

namespace App\Console\Commands;

use App\Models\Redirect;
use Illuminate\Console\Command;
use App\Http\Integrations\Cloudflare\CloudflareConnector;
use App\Http\Integrations\Cloudflare\Requests\KV\GetRequest;
use App\Http\Integrations\Cloudflare\Requests\KV\ListRequest;

class SyncRedirects extends Command
{
    protected $signature = 'app:sync-redirects';

    protected $description = 'Updates all redirects stored in the redirects table with latest KV value';

    public function handle()
    {
        $cloudflareAccountId = config('cloudflare.account_id');
        $cloudflareNamespaceID = config('cloudflare.kv_redirects_id');

        $cloudflare = new CloudflareConnector;
        $listItemsRequest = new ListRequest($cloudflareAccountId, $cloudflareNamespaceID);

        $response = $cloudflare->send($listItemsRequest);
        $body = $response->object();

        $allResults = [];
        $cursor = $body->result_info->cursor;

        while ((bool) $cursor) {
            $this->info('requesting batch');
            $listItemsRequest = new ListRequest($cloudflareAccountId, $cloudflareNamespaceID);
            $listItemsRequest->query()->add('cursor', $cursor);
            $response = $cloudflare->send($listItemsRequest);

            $body = $response->object();
            $allResults = [...$allResults, ...$body->result];

            $cursor = $body->result_info->cursor;
        }
        $allResultsCount = count($allResults);
        $this->info("Discovered {$allResultsCount} items.");

        // Collect all redirects data first
        $redirectsData = [];
        foreach ($allResults as $result) {
            $source = $result->name;

            $getItemRequest = new GetRequest($cloudflareAccountId, $cloudflareNamespaceID, urlencode($source));
            $valueResponse = $cloudflare->send($getItemRequest);
            $destination = $valueResponse->body();

            $redirectsData[] = [
                'source' => $source,
                'destination' => $destination,
                'updated_at' => now(),
                'created_at' => now(),
            ];
        }

        // Perform bulk upsert
        $this->info("Performing bulk upsert of {$allResultsCount} redirects...");
        Redirect::upsert(
            $redirectsData,
            ['source'], // unique key
            ['destination', 'updated_at'] // columns to update if record exists
        );

        $this->info('Sync completed successfully.');
    }
}
