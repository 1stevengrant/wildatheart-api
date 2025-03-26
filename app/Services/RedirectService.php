<?php

namespace App\Services;

use App\Http\Integrations\Cloudflare\CloudflareConnector;
use App\Http\Integrations\Cloudflare\Requests\KV\DeleteRequest;
use App\Http\Integrations\Cloudflare\Requests\KV\PutRequest;
use App\Models\Redirect;
use Exception;
use Illuminate\Support\Facades\Storage;

class RedirectService
{
  private string $cloudflareAccountId;

  private string $cloudflareNamespaceID;

  public function __construct(private CloudflareConnector $cloudflare)
  {
    $this->cloudflareAccountId = config('cloudflare.account_id');
    $this->cloudflareNamespaceID = config('cloudflare.kv_redirects_id');
  }

  public function getRedirectsFromFile(string $file)
  {
    if (!Storage::exists($file)) {
      throw new Exception("File not found: $file");
      return;
    }

    $contents = Storage::get($file);

    $redirects = explode("\n", $contents);
    $validRedirects = collect($redirects)->map(function ($redirect) {

      $parsedRedirect = explode(" ", $redirect);
      return [
        'source' => $parsedRedirect[0],
        'destination' => $parsedRedirect[1]
      ];
    });
    return $validRedirects;
  }

  public function createInCloudflare(string $source, string $destination)
  {
    $redirect = [
      "source" => $source,
      "destination" => $destination
    ];
    $putRequest = new PutRequest($this->cloudflareAccountId, $this->cloudflareNamespaceID, $redirect);
    $response = $this->cloudflare->send($putRequest);
    $body = $response->object();

    if (!$body->success) {
      throw new Exception(json_encode($body->errors));
    }
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

  public function create(string $source, string $destination): int
  {
    $this->createInCloudflare($source, $destination);

    // if that works, add it to the DB
    $redirect = Redirect::upsert(
      [
        "source" => $source,
        "destination" => $destination
      ],
      ['source'],
      ['destination', 'updated_at']
    );
    return $redirect;
  }

  public function delete($source): void
  {
    $this->deleteFromCloudflare($source);
    Redirect::where('source', $source)->delete();
  }
}
