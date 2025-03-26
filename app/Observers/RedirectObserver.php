<?php

namespace App\Observers;

use App\Http\Integrations\Cloudflare\CloudflareConnector;
use App\Http\Integrations\Cloudflare\Requests\KV\DeleteRequest;
use App\Http\Integrations\Cloudflare\Requests\KV\PutRequest;
use App\Models\Redirect;
use Exception;

class RedirectObserver
{

    private string $cloudflareAccountId;
    private string $cloudflareNamespaceID;

    public function __construct(private CloudflareConnector $cloudflare)
    {
        $this->cloudflareAccountId = config("cloudflare.account_id");
        $this->cloudflareNamespaceID = config("cloudflare.kv_redirects_id");
    }

    public function created(Redirect $redirect): void
    {
        $source = $redirect->source;

        $putRequest = new PutRequest($this->cloudflareAccountId, $this->cloudflareNamespaceID, urlencode($source));
        $putRequest->body()->add("value", $redirect->destination);
        $response = $this->cloudflare->send($putRequest);
        $body = $response->object();
        if (!$body->success) {
            throw new Exception(json_encode($body->errors));
        }
    }

    public function updated(Redirect $redirect): void
    {
        $this->created($redirect);
    }

    public function deleted(Redirect $redirect): void
    {
        $source = $redirect->source;
        $deleteRequest = new DeleteRequest($this->cloudflareAccountId, $this->cloudflareNamespaceID, urlencode($source));
        $response = $this->cloudflare->send($deleteRequest);
        $body = $response->object();
        if (!$body->success) {
            throw new Exception(json_encode($body->errors));
        }
    }
}
