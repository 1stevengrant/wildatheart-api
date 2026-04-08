<?php

namespace App\Ai\Middleware;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Support\Facades\Http;

class EnsureToolInputIsObject
{
    /**
     * Fix empty tool_use input arrays being serialized as [] instead of {}.
     *
     * This works around a bug in laravel/ai where the Anthropic gateway
     * sends tool_use.input as a PHP array, which serializes to JSON []
     * instead of {} when empty. Anthropic requires it to be a JSON object.
     */
    public static function register(): void
    {
        Http::globalRequestMiddleware(function ($request) {
            $uri = (string) $request->getUri();

            if (! str_contains($uri, 'api.anthropic.com')) {
                return $request;
            }

            $body = (string) $request->getBody();

            if (empty($body)) {
                return $request;
            }

            $data = json_decode($body, true);

            if (! is_array($data) || ! isset($data['messages'])) {
                return $request;
            }

            $modified = false;

            foreach ($data['messages'] as &$message) {
                if (! isset($message['content']) || ! is_array($message['content'])) {
                    continue;
                }

                foreach ($message['content'] as &$block) {
                    if (($block['type'] ?? '') === 'tool_use' && isset($block['input']) && $block['input'] === []) {
                        $block['input'] = new \stdClass;
                        $modified = true;
                    }
                }
            }

            if (! $modified) {
                return $request;
            }

            return $request->withBody(
                Utils::streamFor(json_encode($data)),
                'application/json'
            );
        });
    }
}
