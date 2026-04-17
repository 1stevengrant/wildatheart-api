<?php

namespace App\Http\Controllers\API\V1\Content\Concerns;

use Statamic\Facades\Token;
use Facades\Statamic\CP\LivePreview;

trait ResolvesLivePreview
{
    /**
     * Resolve a live preview entry from the current request's token.
     *
     * Returns the preview entry if token and live-preview params are present
     * and valid, or null otherwise.
     */
    protected function resolveLivePreview(): mixed
    {
        $request = request();
        $token = $request->query('token', false);
        $livePreview = $request->query('live-preview', false);

        if (! $token || ! $livePreview) {
            return null;
        }

        $token = Token::find($token);

        if (! $token) {
            return null;
        }

        return LivePreview::item($token);
    }
}
