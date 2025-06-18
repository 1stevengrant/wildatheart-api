<?php

namespace App\Services;

use Exception;
use JsonException;
use App\Models\Redirect;
use Illuminate\Support\Facades\Storage;
use App\DataTransferObjects\RedirectData;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;
use App\Http\Integrations\Cloudflare\CloudflareConnector;
use App\Http\Integrations\Cloudflare\Requests\KV\PutRequest;
use App\Http\Integrations\Cloudflare\Requests\KV\DeleteRequest;
use App\Http\Integrations\Cloudflare\Requests\KV\PutRequestBulk;


class RedirectService
{
    public function __construct(
        public CloudflareConnector $cloudflare,
        public string $cloudflareAccountId = '',
        public string $cloudflareNamespaceID = ''
    ) {
        $this->cloudflareAccountId = $cloudflareAccountId ?: config('cloudflare.account_id');
        $this->cloudflareNamespaceID = $cloudflareNamespaceID ?: config('cloudflare.kv_redirects_id');
    }

    /**
     * @return RedirectData[]
     *
     * @throws Exception
     */
    public function getRedirectsFromFile(string $file): array
    {
        if (! Storage::exists($file)) {
            throw new Exception("File not found: $file");
        }

        $contents = Storage::get($file);
        $validRedirects = [];

        if (str_ends_with($file, '.json')) {
            $json = json_decode($contents, true);
            $validRedirects = collect($json)->map(function ($redirect) {
                return new RedirectData($redirect['source'], $redirect['destination']);
            });
        } else {
            $redirects = explode("\n", $contents);
            $validRedirects = collect($redirects)->map(function ($redirect) {
                $parsedRedirect = explode(' ', $redirect);
                return new RedirectData($parsedRedirect[0], $parsedRedirect[1]);
            });
        }

        $cloudflareBulkMaximum = 10000;
        if (count($validRedirects) >= $cloudflareBulkMaximum) {
            throw new Exception("The maximum number of items you may import at one time is $cloudflareBulkMaximum");
        }


        return $validRedirects->all();
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     */
    public function create(string $source, string $destination): int
    {
        $this->createInCloudflare($source, $destination);

        // if that works, add it to the DB
        return Redirect::upsert(
            [
                'source' => $source,
                'destination' => $destination,
            ],
            ['source'],
            ['destination', 'updated_at']
        );
    }

    /**
     * @param  RedirectData[]  $redirects
     *
     * @throws Exception
     */
    public function createBulk(array $redirects): object
    {

        $body = $this->createBulkInCloudflare($redirects);

        $unsuccessfulKeys = $body->result->unsuccessful_keys;

        $successfulRedirects = collect($redirects)
            ->filter(fn($redirect) => ! in_array($redirect->source, $unsuccessfulKeys))
            ->toArray();

        Redirect::upsert(
            $successfulRedirects,
            ['source'],
            ['destination', 'updated_at']
        );

        return $body;
    }

    /**
     * @throws Exception
     */
    public function delete($source): void
    {
        $this->deleteFromCloudflare($source);
        Redirect::where('source', $source)->delete();
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     * @throws Exception
     */
    public function createInCloudflare(string $source, string $destination): void
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
     * @param  RedirectData[]  $redirects
     *
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     * @throws Exception
     */
    public function createBulkInCloudflare(array $redirects): mixed
    {
        $putBulkRequest = new PutRequestBulk($this->cloudflareAccountId, $this->cloudflareNamespaceID, $redirects);
        $response = $this->cloudflare->send($putBulkRequest);
        $body = $response->object();

        if (! $body->success) {
            throw new Exception(json_encode($body->errors));
        }

        return $body;
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws JsonException
     * @throws Exception
     */
    public function deleteFromCloudflare(string $source): void
    {
        $deleteRequest = new DeleteRequest($this->cloudflareAccountId, $this->cloudflareNamespaceID, urlencode($source));
        $response = $this->cloudflare->send($deleteRequest);
        $body = $response->object();
        if (! $body->success) {
            throw new Exception(json_encode($body->errors));
        }
    }
}
