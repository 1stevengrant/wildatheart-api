<?php

namespace App\Services;

use Statamic\Eloquent\Assets\Asset;

class BunnyCDNService
{
    /**
     * Convert asset URL to BunnyCDN URL
     *
     * @param  Asset  $asset  The asset to get the BunnyCDN URL for
     * @return string The BunnyCDN URL
     */
    public function getBunnyCDNUrl(Asset|string|null $asset)
    {
        if (! $asset) {
            return null;
        }

        $BUNNY_CDN_URL = config('services.bunny.s3_cdn_url');

        if (is_string($asset)) {
            return $BUNNY_CDN_URL . '/' . $asset;
        }

        return $BUNNY_CDN_URL . '/' . $asset->path;
    }
}
