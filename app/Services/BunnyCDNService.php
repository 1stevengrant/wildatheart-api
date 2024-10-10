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
    public function getBunnyCDNUrl(Asset $asset)
    {
        $BUNNY_CDN_URL = config('services.bunny.s3_cdn_url');

        return $BUNNY_CDN_URL.'/'.$asset->path;
    }
}
