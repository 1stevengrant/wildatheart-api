<?php

namespace App\Services;

use Exception;
use App\Models\Redirect;
use Illuminate\Support\Facades\Storage;
use App\DataTransferObjects\RedirectData;
use App\Http\Integrations\Cloudflare\CloudflareConnector;
use App\Http\Integrations\Cloudflare\Requests\KV\PutRequest;
use App\Http\Integrations\Cloudflare\Requests\KV\DeleteRequest;
use App\Http\Integrations\Cloudflare\Requests\KV\PutRequestBulk;

class RedirectService
{
    private string $cloudflareAccountId;

    private string $cloudflareNamespaceID;

    public function __construct(private CloudflareConnector $cloudflare)
    {
        $this->cloudflareAccountId = config('cloudflare.account_id');
        $this->cloudflareNamespaceID = config('cloudflare.kv_redirects_id');
    }

    /**
     * @return \App\DataTransferObjects\RedirectData[]
     */
    public function getRedirectsFromFile(string $file): array
    {
        if (! Storage::exists($file)) {
            throw new Exception("File not found: $file");
        }

        $contents = Storage::get($file);

        $redirects = explode("\n", $contents);

        $cloudflareBulkMaxium = 10000;
        if (count($redirects) === $cloudflareBulkMaxium) {
            throw new Exception("The maximum number of items you may import at one time is $cloudflareBulkMaxium");
        }

        $validRedirects = collect($redirects)->map(function ($redirect) {
            $parsedRedirect = explode(' ', $redirect);

            return new RedirectData($parsedRedirect[0], $parsedRedirect[1]);
        });

        return $validRedirects->toArray();
    }

    public function create(string $source, string $destination): int
    {
        $this->createInCloudflare($source, $destination);

        // if that works, add it to the DB
        $redirect = Redirect::upsert(
            [
                'source' => $source,
                'destination' => $destination,
            ],
            ['source'],
            ['destination', 'updated_at']
        );

        return $redirect;
    }

    /**
     * @param  \App\DataTransferObjects\RedirectData[]  $redirects
     */
    public function createBulk(array $redirects): object
    {

        $body = $this->createBulkInCloudflare($redirects);

        $unsuccessfulKeys = $body->result->unsuccessful_keys;

        $successfulRedirects = collect($redirects)
            ->filter(fn ($redirect) => ! in_array($redirect->source, $unsuccessfulKeys))
            ->toArray();

        $redirect = Redirect::upsert(
            $successfulRedirects,
            ['source'],
            ['destination', 'updated_at']
        );

        return $body;
    }

    public function delete($source): void
    {
        $this->deleteFromCloudflare($source);
        Redirect::where('source', $source)->delete();
    }

    public function createInCloudflare(string $source, string $destination)
    {
        $redirect = new RedirectData($source, $destination);
        $putRequest = new PutRequest($this->cloudflareAccountId, $this->cloudflareNamespaceID, $redirect);
        $response = $this->cloudflare->send($putRequest);
        $body = $response->object();

        if (! $body->success) {
            throw new Exception(json_encode($body->errors));
        }
    }

    /**
     * @param  \App\DataTransferObjects\RedirectData[]  $redirects
     * */
    public function createBulkInCloudflare(array $redirects)
    {
        $putBulkRequest = new PutRequestBulk($this->cloudflareAccountId, $this->cloudflareNamespaceID, $redirects);
        $response = $this->cloudflare->send($putBulkRequest);
        $body = $response->object();

        if (! $body->success) {
            throw new Exception(json_encode($body->errors));
        }

        return $body;
    }

    public function deleteFromCloudflare(string $source)
    {
        $deleteRequest = new DeleteRequest($this->cloudflareAccountId, $this->cloudflareNamespaceID, urlencode($source));
        $response = $this->cloudflare->send($deleteRequest);
        $body = $response->object();
        if (! $body->success) {
            throw new Exception(json_encode($body->errors));
        }
    }
}
